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
use App\Models\App;

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
     * @param App $app
     * @return UssdAccountResources|array
     */
    public function showUssdAccounts(ShowUssdAccountsRequest $request, App $app): UssdAccountResources|array
    {
        return $this->service->showUssdAccounts($app, $request->validated());
    }

    /**
     * Show ussd accounts summary.
     *
     * @param ShowUssdAccountsSummaryRequest $request
     * @param App $app
     * @return array
     */
    public function showUssdAccountsSummary(ShowUssdAccountsSummaryRequest $request, App $app): array
    {
        return $this->service->showUssdAccountsSummary($app, $request->validated());
    }

    /**
     * Block ussd accounts.
     *
     * @param BlockUssdAccountsRequest $request
     * @param App $app
     * @return array
     */
    public function blockUssdAccounts(BlockUssdAccountsRequest $request, App $app): array
    {
        $accountIds = $request->input('ussd_account_ids', []);
        return $this->service->blockUssdAccounts($app, $accountIds);
    }

    /**
     * Delete ussd accounts.
     *
     * @param DeleteUssdAccountsRequest $request
     * @param App $app
     * @return array
     */
    public function deleteUssdAccounts(DeleteUssdAccountsRequest $request, App $app): array
    {
        $accountIds = $request->input('ussd_account_ids', []);
        return $this->service->deleteUssdAccounts($app, $accountIds);
    }

    /**
     * Show ussd account.
     *
     * @param ShowUssdAccountRequest $request
     * @param App $app
     * @param UssdAccount $ussd_account
     * @return UssdAccountResource
     */
    public function showUssdAccount(ShowUssdAccountRequest $request, App $app, UssdAccount $ussd_account): UssdAccountResource
    {
        return $this->service->showUssdAccount($app, $ussd_account);
    }

    /**
     * Block ussd account.
     *
     * @param BlockUssdAccountRequest $request
     * @param App $app
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function blockUssdAccount(BlockUssdAccountRequest $request, App $app, UssdAccount $ussd_account): array
    {
        return $this->service->blockUssdAccount($app, $ussd_account);
    }

    /**
     * Unblock ussd account.
     *
     * @param UnblockUssdAccountRequest $request
     * @param App $app
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function unblockUssdAccount(UnblockUssdAccountRequest $request, App $app, UssdAccount $ussd_account): array
    {
        return $this->service->unblockUssdAccount($app, $ussd_account);
    }

    /**
     * Delete ussd account.
     *
     * @param DeleteUssdAccountRequest $request
     * @param App $app
     * @param UssdAccount $ussd_account
     * @return array
     */
    public function deleteUssdAccount(DeleteUssdAccountRequest $request, App $app, UssdAccount $ussd_account): array
    {
        return $this->service->deleteUssdAccount($app, $ussd_account);
    }

    /**
     * Show ussd account summary.
     *
     * @param ShowUssdAccountSummaryRequest $request
     * @param App $app
     * @return array
     */
    public function showUssdAccountSummary(ShowUssdAccountSummaryRequest $request, App $app, UssdAccount $ussd_account): array
    {
        return $this->service->showUssdAccountSummary($app, $ussd_account);
    }
}
