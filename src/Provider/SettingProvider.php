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

    public function getUsers(): Response
    {
        return $this->dispatcher->get('/users.json');
    }

    public function getAccount(): Response
    {
        return $this->dispatcher->get('/account.json');
    }

    public function getBankAccounts(): Response
    {
        return $this->dispatcher->get('/bank_accounts.json');
    }

    public function getInvoiceNumberFormats(): Response
    {
        return $this->dispatcher->get('/number_formats/invoices.json');
    }
}
