<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Brain\Customer\Processes\CreateCustomerProcess;
use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function handleRecordCreation(array $data): Customer
    {
        // 👉 Aqui você controla como o Customer será criado
        CreateCustomerProcess::dispatch([
            'name' => $data['name'],
        ]);

        $user = Customer::create($data);

        // Exemplo: enviar notificação ou qualquer outra lógica
        Notification::make()
            ->title("Cliente {$data['name']} criado com sucesso!")
            ->success()
            ->send();

        return $user;
    }
}
