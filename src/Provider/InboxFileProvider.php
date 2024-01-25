<?php

namespace Fakturoid\Provider;

use Fakturoid\DispatcherInterface;
use Fakturoid\Response;

final class InboxFileProvider extends Provider
{
    public function __construct(
        private readonly DispatcherInterface $dispatcher
    ) {
    }

    public function list(): Response
    {
        return $this->dispatcher->get('/inbox_files.json');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Response
    {
        return $this->dispatcher->post('/inbox_files.json', $data);
    }

    public function sendToOCR(int $id): Response
    {
        return $this->dispatcher->post(sprintf('/inbox_files/%d/send_to_ocr.json', $id));
    }

    public function download(int $id): Response
    {
        return $this->dispatcher->get(sprintf('/inbox_files/%d/download', $id));
    }

    public function delete(int $id): Response
    {
        return $this->dispatcher->delete(sprintf('/inbox_files/%d.json', $id));
    }
}
