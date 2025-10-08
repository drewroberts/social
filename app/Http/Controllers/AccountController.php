<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account): void
    {
        //
    }
}
