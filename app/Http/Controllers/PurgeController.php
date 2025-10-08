<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurgeRequest;
use App\Http\Requests\UpdatePurgeRequest;
use App\Models\Purge;

class PurgeController extends Controller
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
    public function store(StorePurgeRequest $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Purge $purge): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purge $purge): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurgeRequest $request, Purge $purge): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purge $purge): void
    {
        //
    }
}
