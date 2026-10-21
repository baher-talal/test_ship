<span class="badge bg-{{ $shipment->statusColor() }} {{ in_array($shipment->statusColor(), ['warning','info']) ? 'text-dark' : '' }}">{{ $shipment->statusLabel() }}</span>
@if($shipment->isOverdue())<span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ __('Overdue') }}</span>@endif
