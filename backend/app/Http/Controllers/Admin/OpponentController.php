<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repos\OpponentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class OpponentController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $opponentRepo;

    public function __construct(OpponentRepo $opponentRepo)
    {
        $this->opponentRepo = $opponentRepo;
    }

    public function get(Request $request)
    {
        // Use the same Passport guard as routes/admin.php explicitly. This
        // also prevents a misplaced route from falling back to the web guard.
        $admin = Auth::guard('api-Admin')->user();

        if (!$admin) {
            return $this->apiResponseMessage(0, 'Unauthenticated.', 401);
        }

        // A non-super user can never override the branch in this endpoint.
        if ($admin->super != 1) {
            $request['branch_id'] = $admin->branch_id;
        }

        $opponents = $this->opponentRepo->get($request);

        $items = collect($opponents->items())->map(function ($opponent) {
            return [
                'name' => $opponent->name,
                'registered' => true,
                'opponent_type' => $opponent->opponent_type_id ? [
                    'id' => (int) $opponent->opponent_type_id,
                    'name' => $opponent->opponent_type_name,
                ] : null,
                'branch' => $opponent->branch_id ? [
                    'id' => (int) $opponent->branch_id,
                    'name' => $opponent->branch_name,
                ] : null,
                'cases_count' => (int) $opponent->cases_count,
                'clients_count' => (int) $opponent->clients_count,
                'client_names' => $opponent->client_names,
            ];
        });

        return $this->apiResponseData([
            'data' => $items,
            'pagination' => [
                'total' => $opponents->total(),
                'count' => $opponents->count(),
                'per_page' => (int) $opponents->perPage(),
                'current_page' => $opponents->currentPage(),
                'total_pages' => $opponents->lastPage(),
                'is_pagination' => $opponents->hasMorePages(),
            ],
        ]);
    }

    /**
     * Lightweight, read-only contact details for the preview modal.
     * This deliberately avoids the legacy ContactResource object graph.
     */
    public function contact(Request $request)
    {
        $admin = Auth::guard('api-Admin')->user();

        if (!$admin) {
            return $this->apiResponseMessage(0, 'Unauthenticated.', 401);
        }

        $contactId = (int) $request->contact_id;
        if ($contactId < 1) {
            return $this->apiResponseMessage(0, 'Invalid contact id.', 422);
        }

        $query = DB::table('contacts')
            ->leftJoin('clients', 'clients.id', '=', 'contacts.client_id')
            ->leftJoin('admins', 'admins.id', '=', 'contacts.admin_id')
            ->leftJoin('contact_reasons', 'contact_reasons.id', '=', 'contacts.contact_reason_id')
            ->leftJoin('types', 'types.id', '=', 'contacts.contact_type_id')
            ->where('contacts.id', $contactId);

        // Match the branch visibility rules used by the contact list.
        if ($admin->super != 1) {
            $query->where('admins.branch_id', $admin->branch_id);
        }

        $contact = $query->select([
            'contacts.id',
            'contacts.date',
            'contacts.method',
            'contacts.description',
            'clients.id as client_id',
            'clients.name as client_name',
            'clients.phone as client_phone',
            'clients.email as client_email',
            'admins.id as admin_id',
            'admins.name as admin_name',
            'admins.email as admin_email',
            'contact_reasons.id as contact_reason_id',
            'contact_reasons.name as contact_reason_name',
            'types.id as contact_type_id',
            'types.name as contact_type_name',
        ])->first();

        if (!$contact) {
            return $this->apiResponseMessage(0, 'Contact not found.', 404);
        }

        return $this->apiResponseData([
            'id' => (int) $contact->id,
            'date' => $contact->date,
            'method' => (int) $contact->method,
            'description' => $contact->description,
            'client' => $contact->client_id ? [
                'id' => (int) $contact->client_id,
                'name' => $contact->client_name,
                'phone' => $contact->client_phone,
                'email' => $contact->client_email,
            ] : null,
            'admin' => $contact->admin_id ? [
                'id' => (int) $contact->admin_id,
                'name' => $contact->admin_name,
                'email' => $contact->admin_email,
            ] : null,
            'contact_reason' => $contact->contact_reason_id ? [
                'id' => (int) $contact->contact_reason_id,
                'name' => $contact->contact_reason_name,
            ] : null,
            'type' => $contact->contact_type_id ? [
                'id' => (int) $contact->contact_type_id,
                'name' => $contact->contact_type_name,
            ] : null,
        ]);
    }
}
