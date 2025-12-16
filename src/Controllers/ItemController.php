<?php
namespace App\Controllers;

use App\Models\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemController
{
    public function index(Request $req, Response $res, $args)
    {
        $params = $req->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 20)));
        $query = Item::query();

        if (!empty($params['item_code'])) {
            $query->where('item_code', $params['item_code']);
        }
        if (!empty($params['location_id'])) {
            $query->where('location_id', $params['location_id']);
        }
        if (!empty($params['item_status_id'])) {
            $query->where('item_status_id', $params['item_status_id']);
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
        $item = Item::find($id);
        if (!$item) return $this->jsonError($res, 'Item not found', 404);
        $res->getBody()->write($item->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $req, Response $res, $args)
    {
        $data = $req->getParsedBody() ?? [];
        if (empty($data['item_code']) || empty($data['call_number'])) {
            return $this->jsonError($res, 'item_code and call_number are required', 422);
        }
        $item = Item::create(array_merge($data, [
            'input_date' => date('Y-m-d H:i:s'),
            'last_update' => date('Y-m-d H:i:s')
        ]));
        $res->getBody()->write($item->toJson());
        return $res->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $data = $req->getParsedBody() ?? [];
        $item = Item::find($id);
        if (!$item) return $this->jsonError($res, 'Item not found', 404);
        $data['last_update'] = date('Y-m-d H:i:s');
        $item->fill($data);
        $item->save();
        $res->getBody()->write($item->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $item = Item::find($id);
        if (!$item) return $this->jsonError($res, 'Item not found', 404);
        $item->delete();
        $res->getBody()->write(json_encode(['message' => 'deleted']));
        return $res->withHeader('Content-Type', 'application/json');
    }

    private function jsonError(Response $res, $message, $status = 400)
    {
        $res->getBody()->write(json_encode(['error' => $message]));
        return $res->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
