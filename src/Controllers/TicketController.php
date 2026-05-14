<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\EventModel;
use App\Models\TicketModel;
use App\Models\VenueModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class TicketController
{
    public function __construct(
        private Environment $twig,
        private TicketModel $ticketModel,
        private EventModel $eventModel,
        private VenueModel $venueModel,
        private string $basePath,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $tickets    = $this->ticketModel->getAllPaginated($perPage, $offset);
        $total      = $this->ticketModel->countAll();
        $totalPages = (int) ceil($total / $perPage);

        $html = $this->twig->render('ticket/index.html.twig', [
            'base_path'    => $this->basePath,
            'tickets'      => $tickets,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'query_params' => $queryParams,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $html = $this->twig->render('ticket/create.html.twig', [
            'base_path' => $this->basePath,
            'events'    => $this->eventModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {

        $data = (array) ($request->getParsedBody() ?? []);

        $errors     = [];
        $saleType   = $data['sale_type']   ?? '';
        $saleAmount = $data['sale_amount'] ?? '';
        $saleStart  = $data['sale_start']  ?? '';
        $saleEnd    = $data['sale_end']    ?? '';

        if (!isset($data['price']) || $data['price'] === '') $errors['price'] = ['Price is required.'];
        elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) $errors['price'] = ['Price must be 0 or greater.'];
        if (empty($data['seat']))     $errors['seat']     = ['Seat is required.'];
        if (empty($data['row']))      $errors['row']      = ['Row is required.'];
        if (empty($data['event_id'])) $errors['event_id'] = ['Please select an event.'];

        if ($saleType !== '') {
            if (!in_array($saleType, ['percent', 'fixed'], true))    $errors['sale_type']   = ['Invalid sale type.'];
            if ($saleAmount === '' || !is_numeric($saleAmount) || (float)$saleAmount <= 0) $errors['sale_amount'] = ['Sale amount must be a positive number.'];
            elseif ($saleType === 'percent' && (float)$saleAmount > 100)                   $errors['sale_amount'] = ['Percentage cannot exceed 100.'];
            if (empty($saleStart)) $errors['sale_start'] = ['Sale start date is required.'];
            if (empty($saleEnd))   $errors['sale_end']   = ['Sale end date is required.'];
            elseif (!empty($saleStart) && $saleEnd <= $saleStart) $errors['sale_end'] = ['End date must be after start date.'];
        }

        if ($errors) {
            $html = $this->twig->render('ticket/create.html.twig', [
                'base_path' => $this->basePath,
                'events'    => $this->eventModel->getAll(),
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $eventId = (int) ($data['event_id'] ?? 0);
        $this->ticketModel->create(
            (float) ($data['price'] ?? 0),
            (string) ($data['seat'] ?? ''),
            (string) ($data['row'] ?? ''),
            $eventId,
            $saleType   !== '' ? $saleType   : null,
            $saleAmount !== '' ? (float) $saleAmount : null,
            $saleStart  !== '' ? $saleStart  : null,
            $saleEnd    !== '' ? $saleEnd    : null,
        );

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.ticket_created'];
        return $response->withHeader('Location', $this->basePath . '/events/' . $eventId . '/tickets')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $ticket = $this->ticketModel->getById((int) $args['id']);

        if (!$ticket) {
            return $response->withHeader('Location', $this->basePath . '/tickets')->withStatus(302);
        }

        $html = $this->twig->render('ticket/edit.html.twig', [
            'base_path' => $this->basePath,
            'ticket'    => $ticket,
            'events'    => $this->eventModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {

        $id   = (int) $args['id'];
        $data = (array) ($request->getParsedBody() ?? []);

        $errors     = [];
        $saleType   = $data['sale_type']   ?? '';
        $saleAmount = $data['sale_amount'] ?? '';
        $saleStart  = $data['sale_start']  ?? '';
        $saleEnd    = $data['sale_end']    ?? '';

        if (!isset($data['price']) || $data['price'] === '') $errors['price'] = ['Price is required.'];
        elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) $errors['price'] = ['Price must be 0 or greater.'];
        if (empty($data['seat']))     $errors['seat']     = ['Seat is required.'];
        if (empty($data['row']))      $errors['row']      = ['Row is required.'];
        if (empty($data['event_id'])) $errors['event_id'] = ['Please select an event.'];

        if ($saleType !== '') {
            if (!in_array($saleType, ['percent', 'fixed'], true))    $errors['sale_type']   = ['Invalid sale type.'];
            if ($saleAmount === '' || !is_numeric($saleAmount) || (float)$saleAmount <= 0) $errors['sale_amount'] = ['Sale amount must be a positive number.'];
            elseif ($saleType === 'percent' && (float)$saleAmount > 100)                   $errors['sale_amount'] = ['Percentage cannot exceed 100.'];
            if (empty($saleStart)) $errors['sale_start'] = ['Sale start date is required.'];
            if (empty($saleEnd))   $errors['sale_end']   = ['Sale end date is required.'];
            elseif (!empty($saleStart) && $saleEnd <= $saleStart) $errors['sale_end'] = ['End date must be after start date.'];
        }

        if ($errors) {
            $ticket = $this->ticketModel->getById($id);
            $html   = $this->twig->render('ticket/edit.html.twig', [
                'base_path' => $this->basePath,
                'ticket'    => $ticket,
                'events'    => $this->eventModel->getAll(),
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $ticket = $this->ticketModel->load($id);

        if ($ticket->id) {
            $ticket->price       = (float) ($data['price'] ?? $ticket->price);
            $ticket->seat        = (string) ($data['seat'] ?? $ticket->seat);
            $ticket->row         = (string) ($data['row'] ?? $ticket->row);
            $ticket->event_id    = (int) ($data['event_id'] ?? $ticket->event_id);
            $ticket->sale_type   = $saleType   !== '' ? $saleType   : null;
            $ticket->sale_amount = $saleAmount !== '' ? (float) $saleAmount : null;
            $ticket->sale_start  = $saleStart  !== '' ? $saleStart  : null;
            $ticket->sale_end    = $saleEnd    !== '' ? $saleEnd    : null;
            $this->ticketModel->save($ticket);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.ticket_updated'];
        return $response->withHeader('Location', $this->basePath . '/tickets/' . $id . '/edit')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $ticket = $this->ticketModel->load((int) $args['id']);
        if ($ticket->id) {
            $this->ticketModel->delete($ticket);
        }
        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.ticket_deleted'];
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function viewDetails(Request $request, Response $response, array $args): Response
    {
        $ticket = $this->ticketModel->getById((int) $args['id']);

        if (!$ticket) {
            return $response->withHeader('Location', $this->basePath . '/tickets')->withStatus(302);
        }

        // Ticket detail page is admin-only for inventory management
        // Regular users should not access this page directly
        if (!\App\Helpers\Auth::isAdmin()) {
            // Redirect regular users to the event page instead
            return $response->withHeader('Location', $this->basePath . '/events/' . $ticket->event_id)->withStatus(302);
        }

        $html = $this->twig->render('ticket/ticket_detail.html.twig', [
            'base_path' => $this->basePath,
            'ticket'    => $ticket,
            'is_admin'  => true,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function byEvent(Request $request, Response $response, array $args): Response
    {
        // Redirect admins from the seat-selection (purchase) page to the ticket inventory (management)
        if (Auth::isAdmin()) {
            return $response->withHeader('Location', $this->basePath . '/tickets')->withStatus(302);
        }

        $eventId     = (int) $args['id'];
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $tickets = array_map(function ($ticket) {
            $ticket->on_sale         = $this->ticketModel->isOnSale($ticket);
            $ticket->effective_price = $this->ticketModel->effectivePrice($ticket);
            return $ticket;
        }, $this->ticketModel->findByEventPaginated($eventId, $perPage, $offset));
        $event = $this->eventModel->getById($eventId);

        if ($event) {
            $event = $this->eventModel->hydrate([$event], $this->venueModel, $this->ticketModel)[0];
        }

        $totalTickets = $this->ticketModel->countByEvent($eventId);
        $totalPages   = (int) ceil($totalTickets / $perPage);

        $html = $this->twig->render('ticket/tickets_by_event.html.twig', [
            'base_path'    => $this->basePath,
            'tickets'      => $tickets,
            'event'        => $event,
            'event_id'     => $eventId, // Fallback
            // Pass the total so the "X tickets" header stays accurate after
            // pagination — tickets|length is now just the page count.
            'total_tickets' => $totalTickets,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'query_params' => $queryParams,
        ]);
        $response->getBody()->write($html);
        return $response;
    }
}
