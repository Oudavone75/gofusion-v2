<?php

namespace App\Services;

use App\Http\Resources\WithdrawalRequestResource;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Http;

class WithdrawalRequestService
{
    public function __construct(private WithdrawalRequest $withdrawal_request, private UserTransactionService $user_transaction_service)
    {
        $this->withdrawal_request = $withdrawal_request;
        $this->user_transaction_service = $user_transaction_service;
    }

    public function storeWithdrawalRequest(array $data, $user)
    {
        $enviroment = env('APP_ENV');
        $base_url = $enviroment === 'production' ? config('services.ethikdo_live.base_url') : config('services.ethikdo.base_url');
        $base_id = $enviroment === 'production' ?  config('services.ethikdo_live.base_id') : config('services.ethikdo.base_id');
        $table_id = $enviroment === 'production' ? config('services.ethikdo_live.table_id') : config('services.ethikdo.table_id');
        $email = $user->email;
        $amount = $data['amount'];
        $current_balance = $this->user_transaction_service->calculateUserTotalBalance($user);
        if ($current_balance < $amount || $current_balance == 0) {
            return [
                'success' => false,
                'message' => trans('general.insufficient_balance'),
                'data' => []
            ];
        }
        $ethikdo_api_request = $this->addEntryInEthikdo($base_url, $base_id, $table_id, $email, $data, $user);
        $api_response = $ethikdo_api_request->json();
        $api_status = $ethikdo_api_request->status();
        if ($api_status >= 400 && $api_status <= 499) {
            return [
                'success' => false,
                'message' => $api_response['error']['message'],
                'data' => []
            ];
        }
        $withdrawal_request = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'withdrawal_purpose' => $data['withdrawal_purpose'],
            'api_response' => $api_response
        ]);
        $withdrawal_request->amount = number_format((float) $withdrawal_request->amount, 2, '.', '');
        $withdrawal_request->withdrawal_date = $withdrawal_request->created_at->format('d-m-Y');

        $this->user_transaction_service->createTransaction($user, $amount, config('constants.TRANSACTION_TYPE.DEBIT'));
        return [
            'success' => true,
            'message' => trans('general.withdrawal_request_created'),
            'data' => $withdrawal_request
        ];
    }

    public function addEntryInEthikdo($base_url, $base_id, $table_id, $email, $data, $user)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.ethikdo.token'),
        ])->post($base_url . '/' . $base_id . '/' . $table_id, [
            'records' => [
                [
                    'fields' => [
                        'e-mail bénéficiaire' => $email,
                        'Date demande' => now()->toDateString(),
                        'Type de carte' => $data['withdrawal_purpose'],
                        'Montant de la carte' => $data['amount'],
                        'Prenom' => $user->first_name
                    ]
                ]
            ]
        ]);
    }

    public function getWithdrawalRequests($user)
    {
        $list = WithdrawalRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(15);
        $data = WithdrawalRequestResource::collection($list->getCollection());
        $response = paginationData(
            data: $data,
            total: $list->total(),
            perPage: $list->perPage(),
            currentPage: $list->currentPage(),
            lastPage: $list->lastPage(),
            from: $list->firstItem() ?? 0,
            to: $list->lastItem() ?? 0,

        );
        return [
            'success' => true,
            'message' => trans('messages.withdrawal_requests_fetched'),
            'data' => $response
        ];
    }
}
