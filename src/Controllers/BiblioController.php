<?php
namespace App\Controllers;

use App\Models\Biblio;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class BiblioController
{
    public function index(Request $req, Response $res, $args)
    {
        $params = $req->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 20)));
        $query = Biblio::query();

        if (!empty($params['q'])) {
            $q = $params['q'];
            $query->whereRaw('MATCH(title, series_title) AGAINST(? IN BOOLEAN MODE)', [$q]);
        }
        $total = $query->count();
        $data = $query->offset(($page-1)*$perPage)->limit($perPage)->get();

        $payload = [
            'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage],
            'data' => $data
        ];
        $res->getBody()->write(json_encode($payload));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $b = Biblio::find($id);
        if (!$b) {
            return $this->jsonError($res, 'Biblio not found', 404);
        }
        $res->getBody()->write($b->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $req, Response $res, $args)
    {
        $data = $req->getParsedBody() ?? [];

        if (empty($data['title'])) {
            return $this->jsonError($res, 'title is required', 422);
        }
        $b = Biblio::create(array_merge($data, [
            'input_date' => date('Y-m-d H:i:s'),
            'last_update' => date('Y-m-d H:i:s'),
        ]));
        $res->getBody()->write($b->toJson());
        return $res->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $data = $req->getParsedBody() ?? [];
        $b = Biblio::find($id);
        if (!$b) return $this->jsonError($res, 'Biblio not found', 404);
        $data['last_update'] = date('Y-m-d H:i:s');
        $b->fill($data);
        $b->save();
        $res->getBody()->write($b->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $b = Biblio::find($id);
        if (!$b) return $this->jsonError($res, 'Biblio not found', 404);
        $b->delete();
        $res->getBody()->write(json_encode(['message' => 'deleted']));
        return $res->withHeader('Content-Type', 'application/json');
    }

    private function jsonError(Response $res, $message, $status = 400)
    {
        $res->getBody()->write(json_encode(['error' => $message]));
        return $res->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
