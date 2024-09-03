<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Controller for managing emails.
 *
 * This class is responsible for performing CRUD (create, read, update, and delete) operations on emails.
 */
class EmailController extends Controller
{
    /**
     * Stores a new email in the database.
     *
     * @param Request $request HTTP request with email data.
     * @return \Illuminate\Http\JsonResponse JSON response with the ID of the created email.
     */
    public function store(Request $request)
    {
        try {
          // Validate the email field.
          $request->validate([
            'affiliate_id' => 'required|integer',
            'envelope' => 'required|string',
            'from' => 'required|string',
            'subject' => 'required|string',
            'to' => 'required|string',
            'email' => 'required|string',
            // 'raw_text' => 'required|string',
          ]);

          // Just the mandatory ones..
          // 'raw_text', must be optional.
          $insertData = $request->only('email', 'affiliate_id', 'envelope', 'from', 'subject', 'email',  'to');
          $insertData['timestamp'] = time();

          // Use a flag to controll the new one..
          if(!isset($insertData['raw_text']) || $insertData['raw_text'] == '') {
            $insertData['raw_text'] = ' '; // Empty string
          }

        } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['ValidationError' => $e->errors()], 500);
        }

        // Insert
        $id = DB::table('successful_emails')->insertGetId($insertData);

        // Process the email raw_data, just pass the new id, maybe this become slow.
        Artisan::call('emails:process', [$id]);

        // Return the response of validation email.
        return $this->show($id);
    }

    /**
     * Finds an email by ID.
     *
     * @param int $id ID of the email to be found.
     * @return \Illuminate\Http\JsonResponse JSON response with the found email or error message.
     */
    public function show($id)
    {
        // Retrieve all columns except 'deleted_at'.
        $columns = array_diff(Schema::getColumnListing('successful_emails'), ['deleted_at']);
        $email = DB::table('successful_emails')->select($columns)->whereNull('deleted_at')->find($id);

        if (!$email) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        return response()->json($email);
    }

    /**
     * Updates an existing email.
     *
     * @param int $id ID of the email to be updated.
     * @param Request $request HTTP request with all data info to update.
     * @return \Illuminate\Http\JsonResponse JSON response with success message.
     */
    public function update($id, Request $request)
    {
      try {

        // Validate the email field.
        $request->validate([
          'affiliate_id' => 'required|integer',
          'envelope' => 'required|string',
          'from' => 'required|string',
          'subject' => 'required|string',
          'to' => 'required|string',
          'email' => 'required|string',
        ]);

        // Field to be updated.
        $updateData = $request->only('email', 'affiliate_id', 'envelope', 'from', 'subject', 'dkim', 'SPF', 'spam_score', 'email', 'raw_text', 'sender_ip', 'to');

      } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['ValidationError' => $e->errors()], 500);
      }

        DB::table('successful_emails')
            ->where('id', $id)
            ->update($updateData);

        return response()->json(['message' => 'Record updated']);
    }

    /**
     * Lists all non-deleted emails.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with the list of emails.
     */
    public function index()
    {

      // Get all collunms except 'deleted_at'
      $columns = array_diff(Schema::getColumnListing('successful_emails'), ['deleted_at']);

      // Implement the condition to not deleted ones and select the correspondent collumns
      $emails = DB::table('successful_emails')->select($columns)->whereNull('deleted_at')->get();
      return response()->json($emails);
    }

    /**
     * Deletes an email by ID.
     *
     * @param int $id ID of the email to be deleted.
     * @return \Illuminate\Http\JsonResponse JSON response with success message.
     */
    public function destroy($id)
    {
        DB::table('successful_emails')
            ->where('id', $id)
            // Required the soft remove..was in document MB..
            ->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Record deleted']);
    }


}
