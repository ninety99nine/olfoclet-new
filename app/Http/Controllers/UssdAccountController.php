<?php

namespace App\Http\Controllers;

use App\Models\UssdAccount;
use App\Services\UssdAccountService;
use App\Http\Resources\UssdAccountResource;
use App\Http\Resources\UssdAccountResources;
use App\Http\Requests\UssdAccount\ShowUssdAccountRequest;
use App\Http\Requests\UssdAccount\ShowUssdAccountsRequest;
use App\Http\Requests\UssdAccount\BlockUssdAccountRequest;
use App\Http\Requests\UssdAccount\BlockUssdAccountsRequest;
use App\Http\Requests\UssdAccount\DeleteUssdAccountRequest;
use App\Http\Requests\UssdAccount\UnblockUssdAccountRequest;
use App\Http\Requests\UssdAccount\DeleteUssdAccountsRequest;
use App\Http\Requests\UssdAccount\ShowUssdAccountSummaryRequest;
use App\Http\Requests\UssdAccount\ShowUssdAccountsSummaryRequest;

class UssdAccountController extends Controller
{
    /**
     * @var UssdAccountService
     */
    protected $service;

    /**
     * UssdAccountController constructor.
     *
     * @param UssdAccountService $service
     */
    public function __construct(UssdAccountService $service)
    {
        $this->service = $service;
    }

    /**
     * Show ussd accounts.
     *
     * @param ShowUssdAccountsRequest $request
     * @return UssdAccountResources|array
     */
    public function showUssdAccounts(ShowUssdAccountsRequest $request): UssdAccountResources|array
    {
        return $this->service->showUssdAccounts($request->validated());
    }

    /**
     * Show ussd accounts summary.
     *
     * @param ShowUssdAccountsSummaryRequest $request
     * @return array
     */
    public function showUssdAccountsSummary(ShowUssdAccountsSummaryRequest $request): array
    {
        return $this->service->showUssdAccountsSummary($request->validated());
    }

    /**
     * Block ussd accounts.
     *
     * @param BlockUssdAccountsRequest $request
     * @return array
     */
    public function blockUssdAccounts(BlockUssdAccountsRequest $request): array
    {
        $accountIds = $request->input('ussd_account_ids', []);
        return $this->service->blockUssdAccounts($accountIds);
    }

    /**
     * Delete ussd accounts.
     *
     * @param DeleteUssdAccountsRequest $request
     * @return array
     */
    public function deleteUssdAccounts(DeleteUssdAccountsRequest $request): array
    {
        $accountIds = $request->input('ussd_account_ids', []);
        return $this->service->deleteUssdAccounts($accountIds);
    }

    /**
     * Show ussd account.
     *
     * @param ShowUssdAccountRequest $request
     * @param UssdAccount $ussd_account
     * @return UssdAccountResource
     */
    public function showUssdAccount(ShowUssdAccountRequest $request, UssdAccount $ussd_account): UssdAccountResource
    {
        return $this->service->showUssdAccount($ussd_account);
    }

    /**
     * Block ussd account.
     *
     * @param BlockUssdAccountRequest $request
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function blockUssdAccount(BlockUssdAccountRequest $request, UssdAccount $ussd_account): array
    {
        return $this->service->blockUssdAccount($ussd_account);
    }

    /**
     * Unblock ussd account.
     *
     * @param UnblockUssdAccountRequest $request
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function unblockUssdAccount(UnblockUssdAccountRequest $request, UssdAccount $ussd_account): array
    {
        return $this->service->unblockUssdAccount($ussd_account);
    }

    /**
     * Delete ussd account.
     *
     * @param DeleteUssdAccountRequest $request
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function deleteUssdAccount(DeleteUssdAccountRequest $request, UssdAccount $ussd_account): array
    {
        return $this->service->deleteUssdAccount($ussd_account);
    }

    /**
     * Show ussd account summary.
     *
     * @param ShowUssdAccountSummaryRequest $request
     * @return array
     */
    public function showUssdAccountSummary(ShowUssdAccountSummaryRequest $request, UssdAccount $ussd_account): array
    {
        return $this->service->showUssdAccountSummary($ussd_account);
    }
}
