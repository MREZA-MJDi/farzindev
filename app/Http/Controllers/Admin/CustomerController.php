<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->latest();

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.customers.index',
            compact('customers')
        );
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer): View
    {
        abort_unless(
            $customer->isCustomer(),
            404
        );

        $customer->loadCount(['orders', 'wishlists', 'addresses']);

        // Detail pages stay bounded: show the latest five addresses and one
        // page of recent orders instead of hydrating the customer's full history.
        $customer->setRelation(
            'addresses',
            $customer->addresses()->latest()->limit(5)->get()
        );

        $orders = $customer->orders()
            ->latest()
            ->paginate(10, ['*'], 'orders_page')
            ->withQueryString();

        return view(
            'admin.customers.show',
            compact('customer', 'orders')
        );
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(User $customer): View
    {
        abort_unless(
            $customer->isCustomer(),
            404
        );

        return view(
            'admin.customers.edit',
            compact('customer')
        );
    }

    /**
     * Update the specified customer.
     */
    public function update(
        UpdateCustomerRequest $request,
        User $customer
    ): RedirectResponse {
        abort_unless(
            $customer->isCustomer(),
            404
        );

        $customer->update(
            $request->validated()
        );

        return redirect()
            ->route(
                'admin.customers.show',
                $customer
            )
            ->with(
                'success',
                'اطلاعات مشتری با موفقیت بروزرسانی شد.'
            );
    }
}
