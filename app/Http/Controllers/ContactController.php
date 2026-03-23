<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ContactController extends Controller
{
    /**
     * Store a new contact message from the frontend form.
     */
    public function store(Request $request)
    {
        try {
            // Apply rate limiting (5 per minute per IP) to prevent spam
            if (!\Illuminate\Support\Facades\RateLimiter::attempt(
                'contact-form:' . $request->ip(),
                5,
                function() {}
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demasiados intentos. Por favor, espera un minuto.'
                ], 429);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'course' => 'nullable|string|max:100',
                'message' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor, corrige los errores en el formulario.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contactMessage = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'course_interest' => $request->course,
                'message' => $request->message,
                'status' => 'unread'
            ]);

            // If ActivityLog was installed, we'd log this. We'll do it natively for now.
            Log::info('New contact message received', [
                'id' => $contactMessage->id,
                'email' => $request->email,
                'course' => $request->course
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving contact message', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el mensaje. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }

    /**
     * Admin/Teacher: List all contact messages.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = ContactMessage::query();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $messages = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $viewPath = auth()->user()->hasRole('Admin') 
            ? 'admin.messages.index' 
            : 'teacher.messages.index';
            
        return view($viewPath, compact('messages', 'status'));
    }

    /**
     * Admin/Teacher: Show a single message.
     */
    public function show(ContactMessage $message)
    {
        if ($message->status === 'unread') {
            $message->update(['status' => 'read']);
        }
        
        $viewPath = auth()->user()->hasRole('Admin') 
            ? 'admin.messages.show' 
            : 'teacher.messages.show';
            
        return view($viewPath, compact('message'));
    }

    /**
     * Admin/Teacher: Reply to a message.
     */
    public function reply(Request $request, ContactMessage $message)
    {
        $request->validate([
            'reply_message' => 'required|string|max:5000',
        ]);

        try {
            // Update the message record
            $message->update([
                'reply_message' => $request->reply_message,
                'status' => 'replied',
                'replied_at' => Carbon::now(),
                'replied_by' => auth()->id()
            ]);

            // Attempt to send email
            try {
                \Illuminate\Support\Facades\Mail::to($message->email)
                    ->send(new \App\Mail\ContactMessageReply(
                        $message, 
                        $request->reply_message
                    ));
                    
                Log::info('Contact message reply emailed successfully', ['id' => $message->id]);
                $flashMessage = 'Respuesta enviada correctamente por correo electrónico.';
            } catch (\Exception $e) {
                Log::error('Failed to send contact reply email', [
                    'id' => $message->id,
                    'error' => $e->getMessage()
                ]);
                $flashMessage = 'Respuesta guardada, pero no se pudo enviar el correo (revisa la configuración SMTP).';
            }

            return redirect()
                ->route(auth()->user()->hasRole('Admin') ? 'admin.contact-messages.index' : 'teacher.contact-messages.index')
                ->with('status', $flashMessage);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al guardar la respuesta.');
        }
    }
    /**
     * Admin/Teacher: Delete a message.
     */
    public function destroy(Request $request, ContactMessage $message)
    {
        try {
            $message->delete();
            
            Log::info('Contact message deleted', [
                'id' => $message->id,
                'deleted_by' => auth()->id()
            ]);
            
            $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
            
            return redirect()
                ->route("{$routePrefix}.contact-messages.index")
                ->with('status', 'Mensaje eliminado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete contact message', [
                'id' => $message->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al eliminar el mensaje.');
        }
    }
}
