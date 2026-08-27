<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return summary statistics for the authenticated owner.
     * GET /api/owner/dashboard
     *
     * Everything here is a scoped aggregate — the endpoint never loads whole
     * tables to count them in PHP. Only the two "recent" lists fetch rows,
     * and they are capped at five.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $owner = $request->user();

        $unitsByStatus = Unit::query()->ownedBy($owner)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $contractsByStatus = Contract::query()->ownedBy($owner)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $requestsByStatus = PurchaseRequest::query()->ownedBy($owner)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $paymentsByStatus = Payment::query()->ownedBy($owner)
            ->selectRaw('status, COUNT(*) as aggregate, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $paymentCount = fn (string $status) => (int) ($paymentsByStatus[$status]->aggregate ?? 0);
        $paymentTotal = fn (string $status) => round((float) ($paymentsByStatus[$status]->total ?? 0), 2);

        return response()->json([
            'data' => [
                'properties' => [
                    'total'       => Property::query()->ownedBy($owner)->count(),
                    'published'   => Property::query()->ownedBy($owner)->where('is_published', true)->count(),
                    'unpublished' => Property::query()->ownedBy($owner)->where('is_published', false)->count(),
                ],

                'buildings' => [
                    'total' => Building::query()->ownedBy($owner)->count(),
                ],

                'units' => [
                    'total'     => (int) $unitsByStatus->sum(),
                    'available' => (int) ($unitsByStatus['available'] ?? 0),
                    'occupied'  => (int) ($unitsByStatus['occupied'] ?? 0),
                    'reserved'  => (int) ($unitsByStatus['reserved'] ?? 0),
                ],

                'customers' => [
                    'total' => $this->relatedCustomerCount($owner),
                ],

                'contracts' => [
                    'total'      => (int) $contractsByStatus->sum(),
                    'active'     => (int) ($contractsByStatus['active'] ?? 0),
                    'expired'    => (int) ($contractsByStatus['expired'] ?? 0),
                    'terminated' => (int) ($contractsByStatus['terminated'] ?? 0),
                ],

                'purchase_requests' => [
                    'total'     => (int) $requestsByStatus->sum(),
                    'pending'   => (int) ($requestsByStatus['pending'] ?? 0),
                    'approved'  => (int) ($requestsByStatus['approved'] ?? 0),
                    'rejected'  => (int) ($requestsByStatus['rejected'] ?? 0),
                    'cancelled' => (int) ($requestsByStatus['cancelled'] ?? 0),
                ],

                'payments' => [
                    'total'           => (int) $paymentsByStatus->sum('aggregate'),
                    'paid_count'      => $paymentCount('paid'),
                    'pending_count'   => $paymentCount('pending'),
                    'overdue_count'   => $paymentCount('overdue'),
                    'cancelled_count' => $paymentCount('cancelled'),
                    'collected_amount' => $paymentTotal('paid'),
                    'pending_amount'   => $paymentTotal('pending'),
                    'overdue_amount'   => $paymentTotal('overdue'),
                ],

                // What the owner should be billing each month, from live leases.
                'monthly_expected_rent' => round(
                    (float) Contract::query()->ownedBy($owner)->where('status', 'active')->sum('monthly_rent'),
                    2
                ),

                'recent_payments' => PaymentResource::collection(
                    Payment::query()->ownedBy($owner)
                        ->with(['contract.user', 'contract.unit'])
                        ->latest('due_date')
                        ->limit(5)
                        ->get()
                ),

                'recent_purchase_requests' => PurchaseRequestResource::collection(
                    PurchaseRequest::query()->ownedBy($owner)
                        ->with(['customer', 'unit.building.property'])
                        ->latest()
                        ->limit(5)
                        ->get()
                ),

                // Per-property unit breakdown, done in one grouped query.
                'property_overview' => $this->propertyOverview($owner),
            ],
        ]);
    }

    private function relatedCustomerCount(User $owner): int
    {
        return User::query()
            ->where('role', 'customer')
            ->where(fn (Builder $query) => $query
                ->whereIn('id', Contract::query()->ownedBy($owner)->select('user_id'))
                ->orWhereIn('id', PurchaseRequest::query()->ownedBy($owner)->select('customer_id')))
            ->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function propertyOverview(User $owner): array
    {
        $unitCounts = Unit::query()
            ->ownedBy($owner)
            ->join('buildings', 'buildings.id', '=', 'units.building_id')
            ->selectRaw('buildings.property_id, units.status, COUNT(*) as aggregate')
            ->groupBy('buildings.property_id', 'units.status')
            ->get()
            ->groupBy('property_id');

        return Property::query()
            ->ownedBy($owner)
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'status', 'is_published'])
            ->map(function (Property $property) use ($unitCounts) {
                $counts = $unitCounts[$property->id] ?? collect();

                return [
                    'id'           => $property->id,
                    'name'         => $property->name,
                    'city'         => $property->city,
                    'status'       => $property->status,
                    'is_published' => (bool) $property->is_published,
                    'units'        => [
                        'total'     => (int) $counts->sum('aggregate'),
                        'available' => (int) ($counts->firstWhere('status', 'available')->aggregate ?? 0),
                        'occupied'  => (int) ($counts->firstWhere('status', 'occupied')->aggregate ?? 0),
                        'reserved'  => (int) ($counts->firstWhere('status', 'reserved')->aggregate ?? 0),
                    ],
                ];
            })
            ->all();
    }
}
