<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Message;

class TicketController extends Controller
{
    //

    public function createTicket(Request $request){
    	DB::beginTransaction();
		try{
            if(auth()->user()->user_type!="Customer"){
                $response['status'] = 'error';
                $response['response'] = 'Support user cannot access this API';
                return response()->json($response, 401);
            }
	        $validator = Validator::make($request->all(), [
	            'title' => 'required',
	            'description' => 'required'
	        ]);

	        if($validator->fails()){
	        	$response['status'] = 'error';
	        	$response['response'] = $validator->errors(); 
	            return response()->json($response, 401);
	        }

	 		$ticket = Ticket::create([
	 			'user_id' => auth()->user()->id,
	 			'title' => $request->title
	 		]);

	 		$message = Message::create([
	 			'ticket_id' => $ticket->id,
	 			'user_id' => auth()->user()->id,
	 			'message' => $request->description
	 		]);
	 		DB::commit();
	 		$response['status'] = 'success';
        	$response['response'] = 'Ticket created successfully';
	        return response()->json($response, 201);
	    }catch(\Exception $e){
	    	DB::rollback();
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function getTickets(){
    	try{ 
    		$this->updateStatusToAnswered();
    		if(auth()->user()->user_type=='Customer'){
	    		$tickets = Ticket::where('user_id', auth()->user()->id)->get();
    		}
    		else{
    			$tickets = Ticket::join('users', 'users.id', '=', 'tickets.user_id')
    					->select('users.name as name', 'tickets.*')
    					->get();

    		}
    		$response['status'] = 'success';
	        $response['response']['data'] = $tickets;
	        return response()->json($response, 201);
    	}catch(\Exception $e){
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function getTicketDetails(Request $request){
    	try{
    		$ticket_details = Ticket::join('users', 'users.id', '=', 'tickets.user_id')
    						->where('tickets.id', $request->id)
    						->select('users.name as name', 'tickets.title as ticketTitle', 'tickets.status', 'tickets.created_at as ticketCreated', 'tickets.id as ticketId')
    						->get();
    		$message_details = Message::join('users', 'users.id', '=', 'messages.user_id')
    						->where('ticket_id', $request->id)
    						->select('users.name as name', 'messages.message', 'messages.created_at as messageSentAt', 'users.user_type as userType')
    						->orderBy('messages.created_at', 'asc')
    						->get();
    		$response['status'] = 'success';
	        $response['response']['ticket_details'] = $ticket_details;
	        $response['response']['messages'] = $message_details;
	        return response()->json($response, 201);
    	}catch(\Exception $e){
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function sendMessage(Request $request){
    	DB::beginTransaction();
    	try{
    		$validator = Validator::make($request->all(), [
	            'message' => 'required'
	        ]);

	        if($validator->fails()){
	        	$response['status'] = 'error';
	        	$response['response'] = $validator->errors(); 
	            return response()->json($response, 401);
	        }
	        $ticket = Ticket::where('id', $request->ticket_id)->first();
	        if(strtolower($ticket->status)=="spam"){
	        	$response['status'] = 'error';
		    	$response['response'] = "You cannot send message in spam ticket";
		        return response()->json($response, 401);
	        }
    		$message = Message::create([
    			'ticket_id' => $request->ticket_id,
    			'user_id' => auth()->user()->id,
    			'message' => $request->message
    		]);
    		$ticket = Ticket::where('id', $request->ticket_id)->update(['status' => 'In Progress']);
    		if(auth()->user()->user_type=='Support'){
	    		$ticket_data = Ticket::where('id', $request->ticket_id)->first();
	    		$user_data = User::where('id', $ticket_data->user_id)->first();
	    		$details = [
			        'title' => 'Mail from support team',
			        'body' => 'Support send answer of your ticket'
			    ];
			    \Mail::to($user_data->email)->send(new \App\Mail\SupportMessageNotification($details));
			}
    		DB::commit();
    		$response['status'] = 'success';
        	$response['response'] = 'Message sent successfully';
	        return response()->json($response, 201);
    	}catch(\Exception $e){
    		DB::rollback();
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function filterTickets(Request $request){
    	try{
            if(auth()->user()->user_type=="Customer"){
                $response['status'] = 'error';
                $response['response'] = 'Customer user cannot access this API';
                return response()->json($response, 401);
            }
    		$this->updateStatusToAnswered();
    		$tickets = Ticket::join('users', 'users.id', '=', 'tickets.user_id')
    					->select('users.name as name', 'tickets.*')
    					->where('name','LIKE', '%'.$request->name.'%')
    					->where('status','LIKE', '%'.$request->status.'%')
    					->get();
    		$response['status'] = 'success';
	        $response['response']['data'] = $tickets;
	        return response()->json($response, 201);
    	}catch(\Exception $e){
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function updateTicketStatus(Request $request){
    	DB::beginTransaction();
    	try{
            if(auth()->user()->user_type=="Customer"){
                $response['status'] = 'error';
                $response['response'] = 'Customer user cannot access this API';
                return response()->json($response, 401);
            }
    		$validator = Validator::make($request->all(), [
	            'status' => 'required'
	        ]);

	        if($validator->fails()){
	        	$response['status'] = 'error';
	        	$response['response'] = $validator->errors(); 
	            return response()->json($response, 401);
	        }

    		$ticket = Ticket::where('id', $request->ticket_id)->update(['status' => $request->status]);
    		DB::commit();
    		$response['status'] = 'success';
        	$response['response'] = 'Ticket status updated successfully';
	        return response()->json($response, 201);
    	}catch(\Exception $e){
    		DB::rollback();
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function updateStatusToAnswered(){

    	$data = Ticket::where('status', 'In Progress')->get();

    	foreach ($data as $value) {
    		$message = Message::where('ticket_id', $value->id)->orderBy('created_at', 'desc')->first();
    		$user = User::where('id', $message->user_id)->where('user_type', 'Support')->first();
    		if($user){
    			$to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $message->created_at);
				$from = \Carbon\Carbon::now();
				$diff_in_hours = $to->diffInHours($from);
				if($diff_in_hours>=24){
					$update = Ticket::where('id', $message->ticket_id)->update(['status' => 'Answered']);
				}
    		}
    	}
    }
}
