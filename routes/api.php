<?php

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API — Fase 1 (MVP)
|--------------------------------------------------------------------------
*/

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('frontend-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    // ---------- Órdenes de trabajo ----------

    Route::get('/work-orders', function (Request $request) {
        return WorkOrder::with(['client', 'equipment'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);
    });

    // ---------- Cambiar el estado de la orden ----------

    Route::patch('/work-orders/{workOrder}/status', function (Request $request, WorkOrder $workOrder) {
        $data = $request->validate([
            'status' => 'required|in:pendiente,en_proceso,esperando_repuesto,terminado,entregado,cancelado',
        ]);

        $estadoAnterior = $workOrder->status;
        $workOrder->update(['status' => $data['status']]);

        $labels = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'esperando_repuesto' => 'Esperando repuesto',
            'terminado' => 'Terminado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];

        $workOrder->events()->create([
            'user_id' => $request->user()->id,
            'type' => 'cambio_estado',
            'content' => 'Estado cambiado de "' . ($labels[$estadoAnterior] ?? $estadoAnterior) . '" a "' . $labels[$data['status']] . '"',
            'occurred_at' => now(),
        ]);

        return response()->json($workOrder);
    });

    Route::post('/work-orders', function (Request $request) {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'reported_issue' => 'nullable|string',
            'priority' => 'nullable|in:baja,normal,alta,urgente',
            'equipment_category' => 'nullable|string',
            'equipment_brand' => 'nullable|string',
            'equipment_model' => 'nullable|string',
            'equipment_serial' => 'nullable|string',
        ]);

        $tenant = \App\Models\Tenant::first();

        $equipmentId = null;
        if (!empty($data['equipment_category'])) {
            $equipment = \App\Models\Equipment::create([
                'tenant_id' => $tenant->id,
                'client_id' => $data['client_id'],
                'category' => $data['equipment_category'],
                'brand' => $data['equipment_brand'] ?? null,
                'model' => $data['equipment_model'] ?? null,
                'serial_number' => $data['equipment_serial'] ?? null,
            ]);
            $equipmentId = $equipment->id;
        }

        $order = WorkOrder::create([
            'tenant_id' => $tenant->id,
            'client_id' => $data['client_id'],
            'equipment_id' => $equipmentId,
            'reported_issue' => $data['reported_issue'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'created_by' => $request->user()->id,
        ]);

        return response()->json($order->load('client', 'equipment'), 201);
    });
    // ---------- Eventos (línea de tiempo) ----------

    Route::post('/work-orders/{workOrder}/events', function (Request $request, WorkOrder $workOrder) {
        $data = $request->validate([
            'type' => 'required|in:foto,video,audio,nota_texto,nota_voz_transcrita,material_agregado,mano_obra_agregada,cambio_estado,firma,ubicacion',
            'content' => 'nullable|string',
            'media_attachment_id' => 'nullable|exists:media_attachments,id',
            'metadata' => 'nullable|array',
        ]);

        $event = $workOrder->events()->create($data + [
            'user_id' => $request->user()->id,
            'occurred_at' => now(),
        ]);

        return response()->json($event, 201);
    });

    // ---------- Fotografías ----------

    Route::post('/work-orders/{workOrder}/photo', function (Request $request, WorkOrder $workOrder) {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        $disk = config('filesystems.default');
        $path = $request->file('file')->store('work-orders/' . $workOrder->id, $disk);

        $media = \App\Models\MediaAttachment::create([
            'tenant_id' => $workOrder->tenant_id,
            'attachable_type' => WorkOrder::class,
            'attachable_id' => $workOrder->id,
            'type' => 'foto',
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size_bytes' => $request->file('file')->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        $event = $workOrder->events()->create([
            'user_id' => $request->user()->id,
            'type' => 'foto',
            'content' => 'Fotografía capturada durante el trabajo',
            'media_attachment_id' => $media->id,
            'occurred_at' => now(),
        ]);

        return response()->json([
            'event' => $event,
            'media_url' => \Storage::disk($disk)->url($path),
        ], 201);
    });

    // ---------- Materiales y mano de obra ----------

    Route::post('/work-orders/{workOrder}/items', function (Request $request, WorkOrder $workOrder) {
        $data = $request->validate([
            'type' => 'required|in:material,mano_de_obra',
            'description' => 'required|string',
            'quantity' => 'nullable|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item = $workOrder->items()->create([
            'type' => $data['type'],
            'description' => $data['description'],
            'quantity' => $data['quantity'] ?? 1,
            'unit_price' => $data['unit_price'],
        ]);

        $workOrder->events()->create([
            'user_id' => $request->user()->id,
            'type' => $data['type'] === 'material' ? 'material_agregado' : 'mano_obra_agregada',
            'content' => $data['description'] . ' — $' . number_format($item->subtotal, 2),
            'metadata' => ['item_id' => $item->id],
            'occurred_at' => now(),
        ]);

        return response()->json($item, 201);
    });

    Route::delete('/work-orders/{workOrder}/items/{item}', function (WorkOrder $workOrder, \App\Models\WorkOrderItem $item) {
        if ($item->work_order_id !== $workOrder->id) {
            return response()->json(['message' => 'Este ítem no pertenece a esta orden.'], 403);
        }

        $event = $workOrder->events()
            ->whereJsonContains('metadata->item_id', $item->id)
            ->first();

        if ($event) {
            $event->update([
                'metadata' => array_merge($event->metadata ?? [], ['deleted' => true]),
            ]);
        }

        $item->delete();

        return response()->json(['deleted' => true]);
    });
// ---------- Eliminar un evento (foto, nota, etc.) ----------

    Route::delete('/work-orders/{workOrder}/events/{event}', function (WorkOrder $workOrder, \App\Models\WorkOrderEvent $event) {
        if ($event->work_order_id !== $workOrder->id) {
            return response()->json(['message' => 'Este evento no pertenece a esta orden.'], 403);
        }

        // Si el evento tiene una foto asociada, también borramos el archivo real
        if ($event->media_attachment_id && $event->media) {
            \Storage::disk($event->media->disk)->delete($event->media->path);
            $event->media->delete();
        }

        $event->delete();

        return response()->json(['deleted' => true]);
    });
    // ---------- Informe técnico ----------

Route::get('/work-orders/{workOrder}/report', function (WorkOrder $workOrder) {
        $workOrder->load(['client', 'equipment', 'events.media']);

        $events = $workOrder->events->map(function ($event) {
            $event->media_url = $event->media ? \Storage::disk($event->media->disk)->url($event->media->path) : null;
            return $event;
        });

        $technician = \App\Models\User::find($workOrder->assigned_technician_id ?? $workOrder->created_by);

        return response()->json([
            'work_order_id' => $workOrder->id,
            'work_order' => $workOrder->code,
            'status' => $workOrder->status,
            'client' => $workOrder->client,
            'equipment' => $workOrder->equipment,
            'technician_name' => $technician->name ?? null,
            'reported_issue' => $workOrder->reported_issue,
            'created_at' => $workOrder->created_at,
            'finished_at' => $workOrder->finished_at,
            'events' => $events,
            'items' => $workOrder->items,
            'materials_total' => $workOrder->materialsTotal(),
            'labor_total' => $workOrder->laborTotal(),
        ]);
    });
    // ---------- Clientes ----------

    Route::get('/clients', function () {
        return \App\Models\Client::orderBy('first_name')->get();
    });

    Route::post('/clients', function (Request $request) {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $tenant = \App\Models\Tenant::first();

        $client = \App\Models\Client::create($data + ['tenant_id' => $tenant->id]);

        return response()->json($client, 201);
    });
});