<?php

namespace Fakturoid\Provider;

use Fakturoid\DispatcherInterface;
use Fakturoid\Response;

final class SettingProvider extends Provider
{
    public function __construct(
        private readonly DispatcherInterface $dispatcher
    ) {
    }

    public function getUser(int $id): Response
    {
        return $this->dispatcher->get(sprintf('/users/%d.json', $id));
    }

    public function listUsers(): Response
    {
        return $this->dispatcher->get('/users.json');
    }

    public function listAccount(): Response
    {
        return $this->dispatcher->get('/account.json');
    }

    public function listBankAccounts(): Response
    {
        return $this->dispatcher->get('/bank_accounts.json');
    }

    public function listInvoiceNumberFormats(): Response
    {
        return $this->dispatcher->get('/number_formats/invoices.json');
    }
}
