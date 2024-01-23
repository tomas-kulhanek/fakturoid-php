<?php

namespace Fakturoid\Provider;

use Fakturoid\DispatcherInterface;
use Fakturoid\Response;

final class ExpenseProvider extends Provider
{
    public function __construct(
        private readonly DispatcherInterface $dispatcher
    ) {
    }

    /**
     * @param array{
     *  'since'?:string,
     *  'updated_since'?:string,
     *  'page'?:int,
     *  'subject_id'?:int,
     *  'custom_id'?:string,
     *  'number'?:string,
     *  'variable_symbol'?:string,
     *  'status'?:string
     * } $params
     */
    public function list(array $params = []): Response
    {
        $allowed = ['since', 'updated_since', 'page', 'subject_id', 'custom_id', 'number', 'variable_symbol', 'status'];
        return $this->dispatcher->get('/expenses.json', $this->filterOptions($params, $allowed));
    }

    /**
     * @param array{'query'?:string, 'page'?:int, 'tags'?:string[]} $params
     */
    public function search(array $params = []): Response
    {
        return $this->dispatcher->get(
            '/expenses/search.json',
            $this->filterOptions($params, ['query', 'page', 'tags'])
        );
    }

    public function get(int $id): Response
    {
        return $this->dispatcher->get(sprintf('/expenses/%d.json', $id));
    }

    public function getAttachment(int $expenseId, int $attachmentId): Response
    {
        return $this->dispatcher->get(sprintf('/expenses/%d/attachments/%d/download', $expenseId, $attachmentId));
    }

    public function fireAction(int $id, string $event): Response
    {
        return $this->dispatcher->post('/expenses/$id/fire.json', ['event' => $event]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Response
    {
        return $this->dispatcher->post('/expenses.json', $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Response
    {
        return $this->dispatcher->patch(sprintf('/expenses/%d.json', $id), $data);
    }

    public function delete(int $id): Response
    {
        return $this->dispatcher->delete(sprintf('/expenses/%d.json', $id));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPayment(int $expenseId, array $data): Response
    {
        return $this->dispatcher->post(sprintf('/expenses/%d/payments.json', $expenseId), $data);
    }

    public function deletePayment(int $expenseId, int $id): Response
    {
        return $this->dispatcher->delete(sprintf('/expenses/%d/payments/%d.json', $expenseId, $id));
    }
}
