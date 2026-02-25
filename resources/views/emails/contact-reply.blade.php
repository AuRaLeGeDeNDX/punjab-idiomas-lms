@component('mail::message')
# Hola {{ $name }},

Gracias por contactar con **Punjab Idiomas**. 

Aquí tienes nuestra respuesta a tu consulta reciente:

@component('mail::panel')
{{ $replyText }}
@endcomponent

---

**Tu mensaje original:**
> *{{ $originalMessage }}*

---

Si necesitas más ayuda, no dudes en responder directamente a este correo o escribirnos por WhatsApp al [+34 612 45 50 57](https://wa.me/34612455057).

Un saludo,<br>
**El equipo de {{ config('app.name') }}**
@endcomponent
