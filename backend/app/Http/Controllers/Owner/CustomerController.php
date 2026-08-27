<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Contract;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * GET /api/owner/customers
     *
     * Only customers who actually deal with this owner: they hold a contract
     * on one of the owner's units, or they have raised a purchase request
     * against one. Previously this returned every customer in the database.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);

        $owner = $request->user();

        $customers = User::query()
            ->where('role', 'customer')
            ->where(fn (Builder $query) => $query
                ->whereIn('id', Contract::query()->ownedBy($owner)->select('user_id'))
                ->orWhereIn('id', PurchaseRequest::query()->ownedBy($owner)->select('customer_id')))
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn (Builder $q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->withCount([
                'contracts as contracts_count' => fn (Builder $q) => $q->whereIn(
                    'unit_id',
                    Unit::query()->ownedBy($owner)->select('id')
                ),
                'purchaseRequests as purchase_requests_count' => fn (Builder $q) => $q->whereIn(
                    'unit_id',
                    Unit::query()->ownedBy($owner)->select('id')
                ),
            ])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($customers)->response();
    }

    /** GET /api/owner/customers/{customer} */
    public function show(Request $request, User $customer): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);

        $owner = $request->user();

        // A customer id that is not connected to this owner must not resolve,
        // otherwise the list scoping above could be walked around by guessing.
        abort_unless($customer->role === 'customer' && $this->isRelatedTo($owner, $customer), 404, 'Customer not found.');

        $customer->setRelation(
            'contracts',
            Contract::query()->ownedBy($owner)->where('user_id', $customer->id)
                ->with('unit.building.property')->latest()->get()
        );

        $customer->setRelation(
            'purchaseRequests',
            PurchaseRequest::query()->ownedBy($owner)->where('customer_id', $customer->id)
                ->with('unit.building.property')->latest()->get()
        );

        return (new UserResource($customer))->response();
    }

    private function isRelatedTo(User $owner, User $customer): bool
    {
        return Contract::query()->ownedBy($owner)->where('user_id', $customer->id)->exists()
            || PurchaseRequest::query()->ownedBy($owner)->where('customer_id', $customer->id)->exists();
    }
}
