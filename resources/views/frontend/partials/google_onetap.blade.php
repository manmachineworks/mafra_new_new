@if(!Auth::check())
<div style="position: fixed; top: 0; left: 0; z-index: 9999; pointer-events: none;">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleOneTap(res){
    fetch('{{ url('/google-one-tap/callback') }}', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body:JSON.stringify({ credential:res.credential })
    })
    .then(r=>r.json())
    .then(d=>{
        console.log('One Tap response:', d);
        if(d.success){
            window.location.href = '/';
        }
    })
    .catch(e=>console.error(e));
}

document.addEventListener("DOMContentLoaded", function() {
    google.accounts.id.initialize({
        client_id: "{{ env('GOOGLE_CLIENT_ID') }}",
        callback: handleOneTap,
        auto_select: false,
        cancel_on_tap_outside: false
    });

    google.accounts.id.prompt({
        momentCallback: (notification) => {
            console.log('One Tap moment:', notification);
            if(notification.isNotDisplayed() || notification.isSkippedMoment()){
                google.accounts.id.prompt();
            }
        }
    });
});
</script>
</div>
@endif
