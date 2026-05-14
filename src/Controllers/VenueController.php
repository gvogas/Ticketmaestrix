<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\VenueModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class VenueController
{
    public function __construct(
        private Environment $twig,
        private VenueModel $venueModel,
        private string $basePath,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $html = $this->twig->render('venue/index.html.twig', [
            'base_path' => $this->basePath,
            'venues'    => $this->venueModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $html = $this->twig->render('venue/create.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {

        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['name']))     $errors['name']     = ['Name is required.'];
        if (empty($data['address']))  $errors['address']  = ['Address is required.'];
        if (empty($data['capacity'])) $errors['capacity'] = ['Capacity is required.'];
        elseif (!is_numeric($data['capacity']) || (int)$data['capacity'] <= 0) $errors['capacity'] = ['Capacity must be a positive number.'];
        if (!empty($data['lat']) && !is_numeric($data['lat'])) $errors['lat'] = ['Latitude must be a numeric value.'];
        if (!empty($data['lng']) && !is_numeric($data['lng'])) $errors['lng'] = ['Longitude must be a numeric value.'];
        if ($errors) {
            $html = $this->twig->render('venue/create.html.twig', [
                'base_path' => $this->basePath,
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $address = (string) ($data['address'] ?? '');
        $lat     = !empty($data['lat']) ? (float) $data['lat'] : null;
        $lng     = !empty($data['lng']) ? (float) $data['lng'] : null;

        if (($lat === null) && $address !== '') {
            $coords = $this->geocodeAddress($address);
            if ($coords !== null) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
            }
        }

        $this->venueModel->create(
            name:        (string) ($data['name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            imageUrl:    (string) ($data['image_url'] ?? ''),
            address:     $address,
            capacity:    (int) ($data['capacity'] ?? 0),
            lat:         $lat,
            lng:         $lng,
        );

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.venue_created'];
        return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {

        $venue = $this->venueModel->getById((int) $args['id']);
        if (!$venue) {
            return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
        }

        $html = $this->twig->render('venue/edit.html.twig', [
            'base_path' => $this->basePath,
            'venue'     => $venue,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {

        $venue = $this->venueModel->load((int) $args['id']);
        if (!$venue->id) {
            return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
        }

        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['name']))     $errors['name']     = ['Name is required.'];
        if (empty($data['address']))  $errors['address']  = ['Address is required.'];
        if (empty($data['capacity'])) $errors['capacity'] = ['Capacity is required.'];
        elseif (!is_numeric($data['capacity']) || (int)$data['capacity'] <= 0) $errors['capacity'] = ['Capacity must be a positive number.'];
        if (!empty($data['lat']) && !is_numeric($data['lat'])) $errors['lat'] = ['Latitude must be a numeric value.'];
        if (!empty($data['lng']) && !is_numeric($data['lng'])) $errors['lng'] = ['Longitude must be a numeric value.'];
        if ($errors) {
            $html = $this->twig->render('venue/edit.html.twig', [
                'base_path' => $this->basePath,
                'venue'     => $venue,
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $venue->name        = (string) ($data['name'] ?? $venue->name);
        $venue->description = (string) ($data['description'] ?? $venue->description);
        $venue->image_url   = (string) ($data['image_url'] ?? $venue->image_url);
        $venue->address     = (string) ($data['address'] ?? $venue->address);
        $venue->capacity    = (int) ($data['capacity'] ?? $venue->capacity);
        $venue->lat         = !empty($data['lat']) ? (float) $data['lat'] : null;
        $venue->lng         = !empty($data['lng']) ? (float) $data['lng'] : null;

        if ((empty($venue->lat) || empty($venue->lng)) && !empty($venue->address)) {
            $coords = $this->geocodeAddress($venue->address);
            if ($coords !== null) {
                $venue->lat = $coords['lat'];
                $venue->lng = $coords['lng'];
            }
        }

        $this->venueModel->save($venue);

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.venue_updated'];
        return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $venue = $this->venueModel->load((int) $args['id']);
        if ($venue->id) {
            $this->venueModel->delete($venue);
        }
        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.venue_deleted'];
        return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
    }

    public function viewDetails(Request $request, Response $response, array $args): Response
    {
        $venue = $this->venueModel->getById((int) $args['id']);

        if (!$venue) {
            return $response->withHeader('Location', $this->basePath . '/venues')->withStatus(302);
        }

        $html = $this->twig->render('venue/venue_detail.html.twig', [
            'base_path' => $this->basePath,
            'venue'     => $venue,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    private function geocodeAddress(string $address): ?array
    {
        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
        if ($apiKey === '' || $address === '') {
            return null;
        }

        $url     = 'https://maps.googleapis.com/maps/api/geocode/json?address='
                  . urlencode($address) . '&key=' . $apiKey;
        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $raw     = @file_get_contents($url, false, $context);

        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (($data['status'] ?? '') !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
            return null;
        }

        $loc = $data['results'][0]['geometry']['location'];
        return ['lat' => (float) $loc['lat'], 'lng' => (float) $loc['lng']];
    }
}
