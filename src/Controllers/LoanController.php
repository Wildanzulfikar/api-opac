<?php
namespace App\Controllers;

use App\Models\Loan;
use App\Models\Biblio;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoanController {
    public function index(Request $req, Response $res, $args)
    {
        $params = $req->getQueryParams();

        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 20)));
        $search = trim($params['search'] ?? '');

        // ===============================
        // NORMALISASI is_return
        // ===============================
        $isReturn = null;
        if (isset($params['is_return'])) {
            $val = $params['is_return'];
            if ($val === '1' || $val === 1 || $val === true || $val === 'true') {
                $isReturn = 1;
            } elseif ($val === '0' || $val === 0 || $val === false || $val === 'false') {
                $isReturn = 0;
            }
        }

        // ===============================
        // QUERY: ALL LOANS (with filter)
        // ===============================
        $query = Loan::query();

        if (!empty($params['member_id'])) {
            $query->where('member_id', $params['member_id']);
        }

        if ($isReturn !== null) {
            $query->where('is_return', $isReturn);
        }

        // 🔥 SEARCH (member_id)
        if ($search !== '') {
            $query->where('member_id', 'LIKE', "%{$search}%");
        }

        // total
        $total = $query->count();

        // pagination
        $loans = $query
            ->orderByDesc('loan_id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $loans->map(function ($loan) {
            return [
                'loan_id'     => $loan->loan_id,
                'member_id'   => $loan->member_id,
                'item_code'   => $loan->item_code,
                'loan_date'   => $loan->loan_date,
                'due_date'    => $loan->due_date,
                'return_date' => $loan->return_date,
                'status'      => $loan->is_return ? 'Lunas' : 'Belum',
            ];
        });

        $payload = [
            'meta' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
            'data' => $data,
        ];

        $res->getBody()->write(json_encode($payload));
        return $res->withHeader('Content-Type', 'application/json');
    }


    public function show(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $loan = Loan::with('biblio')->find($id);
        if (!$loan) return $this->jsonError($res, 'Loan not found', 404);
        $res->getBody()->write($loan->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $req, Response $res, $args)
    {
        $data = $req->getParsedBody() ?? [];

        if (empty($data['item_code']) || empty($data['member_id']) || empty($data['loan_date']) || empty($data['due_date'])) {
            return $this->jsonError($res, 'item_code, member_id, loan_date, due_date are required', 422);
        }

        $loan = Loan::create(array_merge($data, [
            'input_date' => date('Y-m-d H:i:s'),
            'last_update' => date('Y-m-d H:i:s'),
            'is_lent' => 1,
            'is_return' => 0
        ]));

        $res->getBody()->write($loan->toJson());
        return $res->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function markReturned(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $loan = Loan::find($id);
        if (!$loan) return $this->jsonError($res, 'Loan not found', 404);

        $body = $req->getParsedBody() ?? [];
        $return_date = $body['return_date'] ?? date('Y-m-d');

        $loan->is_return = 1;
        $loan->return_date = $return_date;
        $loan->last_update = date('Y-m-d H:i:s');
        $loan->save();

        $res->getBody()->write($loan->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $data = $req->getParsedBody() ?? [];
        $loan = Loan::find($id);
        if (!$loan) return $this->jsonError($res, 'Loan not found', 404);

        $data['last_update'] = date('Y-m-d H:i:s');
        $loan->fill($data);
        $loan->save();

        $res->getBody()->write($loan->toJson());
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $req, Response $res, $args)
    {
        $id = $args['id'];
        $loan = Loan::find($id);
        if (!$loan) return $this->jsonError($res, 'Loan not found', 404);
        $loan->delete();
        $res->getBody()->write(json_encode(['message' => 'deleted']));
        return $res->withHeader('Content-Type', 'application/json');
    }

    private function jsonError(Response $res, $message, $status = 400)
    {
        $res->getBody()->write(json_encode(['error' => $message]));
        return $res->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
