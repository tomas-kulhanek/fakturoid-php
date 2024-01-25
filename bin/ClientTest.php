<?php


use Fakturoid\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    /* Account */

    public function testGetAccount()
    {
        $f = $this->getClient();
        $account = $f->getAccount();
    }

    public function testGetAccountWithInvalidHeaders()
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \Exception($errstr, $errno);
        }, E_ALL);

        $f = $this->getClient();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown option keys: unknown');
        $f->getSubjects(['unknown' => 'test']); //@phpstan-ignore-line
        restore_error_handler();
    }

    /* Bank Account */

    public function testGetBankAccounts()
    {
        $f = $this->getClient();
        $users = $f->getBankAccounts();
    }

    /* User */

    public function testGetUser()
    {
        $f = $this->getClient();
        $user = $f->getUser(10);
    }

    public function testGetUsers()
    {
        $f = $this->getClient();
        $users = $f->getUsers();
    }

    /* Invoice */

    public function testGetInvoices()
    {
        $f = $this->getClient();
        $invoices = $f->getInvoices();
    }

    public function testGetInvoicesSecondPage()
    {
        $f = $this->getClient();
        $invoices = $f->getInvoices(['page' => 2]);
    }

    public function testGetRegularInvoices()
    {
        $f = $this->getClient();
        $invoices = $f->getRegularInvoices();
    }

    public function testGetProformaInvoices()
    {
        $f = $this->getClient();
        $invoices = $f->getProformaInvoices();
    }

    public function testGetInvoice()
    {
        $f = $this->getClient();
        $invoice = $f->getInvoice(86);
    }

    public function testGetInvoicePdf()
    {
        $f = $this->getClient();
        $pdf = $f->getInvoicePdf(86);
    }

    public function testSearchInvoices()
    {
        $f = $this->getClient();
        $invoices = $f->searchInvoices(['query' => 'Test']);
    }

    public function testUpdateInvoice()
    {
        $f = $this->getClient();
        $invoice = $f->updateInvoice(86, ['due' => 5]);
    }

    public function testFireInvoice()
    {
        $f = $this->getClient();
        $response = $f->fireInvoice(86, 'pay');
        $response = $f->fireInvoice(86, 'pay', ['paid_at' => '2018-03-21T00:00:00+01:00']);
        $response = $f->fireInvoice(
            86,
            'pay',
            [
                'paid_at' => '2018-03-21T00:00:00+01:00',
                'paid_amount' => '1000',
                'variable_symbol' => '12345678',
                'bank_account_id' => 23
            ]
        );
    }

    public function testCreateInvoice()
    {
        $f = $this->getClient();
        $invoice = $f->createInvoice(
            [
                'subject_id' => 36,
                'lines' => [
                    [
                        'quantity' => 5,
                        'unit_name' => 'kg',
                        'name' => 'Sand',
                        'unit_price' => '100',
                        'vat_rate' => 21
                    ]
                ]
            ]
        );
    }

    public function testDeleteInvoice()
    {
        $f = $this->getClient();
        $response = $f->deleteInvoice(86);
    }

    /* Expense */

    public function testGetExpenses()
    {
        $f = $this->getClient();
        $expenses = $f->getExpenses();
    }

    public function testGetExpense()
    {
        $f = $this->getClient();
        $expense = $f->getExpense(201);
    }

    public function testSearchExpenses()
    {
        $f = $this->getClient();
        $expenses = $f->searchExpenses(['query' => 'Test']);
    }

    public function testUpdateExpense()
    {
        $f = $this->getClient();
        $expense = $f->updateExpense(201, ['due' => 5]);
    }

    public function testFireExpense()
    {
        $f = $this->getClient();
        $response = $f->fireExpense(201, 'pay');
        $response = $f->fireExpense(
            201,
            'pay',
            [
                'paid_on' => '2018-03-21',
                'paid_amount' => '1000',
                'variable_symbol' => '12345678',
                'bank_account_id' => 23
            ]
        );
    }

    public function testCreateExpense()
    {
        $f = $this->getClient();
        $expense = $f->createExpense(
            [
                'subject_id' => 36,
                'lines' => [
                    [
                        'quantity' => 5,
                        'unit_name' => 'kg',
                        'name' => 'Sand',
                        'unit_price' => '100',
                        'vat_rate' => 21
                    ]
                ]
            ]
        );
    }

    public function testDeleteExpense()
    {
        $f = $this->getClient();
        $response = $f->deleteExpense(201);
    }

    /* Subject */

    public function testGetSubjects()
    {
        $f = $this->getClient();
        $subjects = $f->getSubjects();
    }

    public function testGetSubject()
    {
        $f = $this->getClient();
        $subject = $f->getSubject(36);
    }

    public function testCreateSubject()
    {
        $f = $this->getClient();
        $subject = $f->createSubject(['name' => 'Apple Czech s.r.o.']);
    }

    public function testUpdateSubject()
    {
        $f = $this->getClient();
        $subject = $f->updateSubject(36, ['street' => 'Tetst']);
    }

    public function testDeleteSubject()
    {
        $f = $this->getClient();
        $response = $f->deleteSubject(36);
    }

    public function testSearchSubjects()
    {
        $f = $this->getClient();
        $subjects = $f->searchSubjects(['query' => 'Apple']);
    }

    /* Generator */

    public function testGetGenerators()
    {
        $f = $this->getClient();
        $generators = $f->getGenerators();
    }

    public function testGetTemplateGenerators()
    {
        $f = $this->getClient();
        $generators = $f->getTemplateGenerators();
    }

    public function testGetRecurringGenerators()
    {
        $f = $this->getClient();
        $generators = $f->getRecurringGenerators();
    }

    public function testGetGenerator()
    {
        $f = $this->getClient();
        $generator = $f->getGenerator(10);
    }

    public function testCreateGenerator()
    {
        $f = $this->getClient();
        $generator = $f->createGenerator(
            [
                'name' => 'Test',
                'subject_id' => 36,
                'payment_method' => 'bank',
                'currency' => 'CZK',
                'lines' => [
                    [
                        'quantity' => 5,
                        'unit_name' => 'kg',
                        'name' => 'Sand',
                        'unit_price' => '100',
                        'vat_rate' => 21
                    ]
                ]
            ]
        );
    }

    public function testUpdateGenerator()
    {
        $f = $this->getClient();
        $generator = $f->updateGenerator(10, ['due' => 5]);
    }

    public function testDeleteGenerator()
    {
        $f = $this->getClient();
        $response = $f->deleteGenerator(10);
    }

    /* Message */

    public function testCreateMessage()
    {
        $f = $this->getClient();
        $message = $f->createMessage(
            86,
            [
                'email' => 'test@example.org',
                'subject' => 'Hello',
                'message' => "Hello,\n\nI have invoice for you.\n#link#\n\n   John Doe"
            ]
        );
    }

    /* Reports */

    public function testGetReports()
    {
        $f = $this->getClient();
        $reports = $f->getReports(2021);
    }

    public function testGetPaidReports()
    {
        $f = $this->getClient();
        $reports = $f->getPaidReports(2021);
    }

    public function testGetVatReports()
    {
        $f = $this->getClient();
        $reports = $f->getVatReports(2021);
    }

    /* Number formats */

    public function testGetInvoiceNumberFormats()
    {
        $f = $this->getClient();
        $users = $f->getInvoiceNumberFormats();
    }

    /* Event */

    public function testGetEvents()
    {
        $f = $this->getClient();
        $events = $f->getEvents();
    }

    public function testGetPaidEvents()
    {
        $f = $this->getClient();
        $events = $f->getPaidEvents();
    }

    /* Todo */

    public function testGetTodos()
    {
        $f = $this->getClient();
        $todos = $f->getTodos();
    }

    /* Inventory items */

    public function testGetInventoryItems()
    {
        $f = $this->getClient();
        $inventoryItems = $f->getInventoryItems();
    }

    public function testGetInventoryItemsBySku()
    {
        $f = $this->getClient();
        $inventoryItems = $f->getInventoryItems(['sku' => 'SKU1234']);
    }

    public function testGetArchivedInventoryItems()
    {
        $f = $this->getClient();
        $inventoryItems = $f->getArchivedInventoryItems();
    }

    public function testGetInventoryItem()
    {
        $f = $this->getClient();
        $inventoryItem = $f->getInventoryItem(26);
    }

    public function testSearchInventoryItems()
    {
        $f = $this->getClient();
        $inventoryItems = $f->searchInventoryItems(['query' => 'Item name']);
    }

    public function testArchiveInventoryItem()
    {
        $f = $this->getClient();
        $inventoryItem = $f->archiveInventoryItem(26);
    }

    public function testUnarchiveInventoryItem()
    {
        $f = $this->getClient();
        $inventoryItem = $f->unArchiveInventoryItem(26);
    }

    public function testUpdateInventoryItem()
    {
        $f = $this->getClient();
        $inventoryItem = $f->updateInventoryItem(26, ['name' => 'Another name']);
    }

    public function testCreateInventoryItem()
    {
        $f = $this->getClient();
        $inventoryItem = $f->createInventoryItem(
            [
                'name' => 'Item name',
                'sku' => 'SKU12345',
                'track_quantity' => true,
                'quantity' => 100,
                'native_purchase_price' => 500,
                'native_retail_price' => 1000
            ]
        );
    }

    public function testDeleteInventoryItem()
    {
        $f = $this->getClient();
        $f->deleteInventoryItem(26);
    }

    /* Inventory moves */

    public function testGetInventoryMoves()
    {
        $f = $this->getClient();
        $inventoryMoves = $f->getInventoryMoves();
    }

    public function testGetInventoryMovesForSingleItem()
    {
        $f = $this->getClient();
        $inventoryMoves = $f->getInventoryMoves(['inventory_item_id' => 26]);
    }

    public function testGetInventoryMove()
    {
        $f = $this->getClient();
        $inventoryMove = $f->getInventoryMove(26, 61);
    }

    public function testUpdateInventoryMove()
    {
        $f = $this->getClient();
        $inventoryMove = $f->updateInventoryMove(26, 61, ['moved_on' => '2023-01-11']);
    }

    public function testCreateInventoryMove()
    {
        $f = $this->getClient();
        $inventoryMove = $f->createInventoryMove(
            26,
            [
                'direction' => 'in',
                'moved_on' => '2023-01-12',
                'quantity_change' => 5,
                'purchase_price' => '249.99',
                'purchase_currency' => 'CZK',
                'private_note' => 'Bought with discount'
            ]
        );
    }

    public function testDeleteInventoryMove()
    {
        $f = $this->getClient();
        $inventoryMove = $f->deleteInventoryMove(26, 61);
    }
}
