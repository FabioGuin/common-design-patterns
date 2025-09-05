<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function find(int $id): ?Order;
    public function findByUser(User $user): Collection;
    public function update(Order $order, array $data): bool;
    public function delete(Order $order): bool;
    public function findByStatus(string $status): Collection;
}

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function find(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByUser(User $user): Collection
    {
        return Order::where('user_id', $user->id)->get();
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function findByStatus(string $status): Collection
    {
        return Order::where('status', $status)->get();
    }
}
