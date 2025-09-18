<!-- Session Timeout Warning Modal -->
<div id="session-timeout-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Sesi Akan Berakhir</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Sesi Anda akan berakhir dalam <span id="countdown" class="font-semibold text-red-600">5</span> menit.
                    Apakah Anda ingin memperpanjang sesi?
                </p>
            </div>
            <div class="flex items-center px-4 py-3">
                <button id="extend-session" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Perpanjang Sesi
                </button>
                <button id="logout-now" class="ml-3 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Keluar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let warningShown = false;
    let countdownInterval;
    const sessionLifetime = {{ session('lifetime', 120) * 60 * 1000 }}; // Convert to milliseconds
    const warningTime = 5 * 60 * 1000; // 5 minutes before expiry
    const logoutTime = sessionLifetime - warningTime;

    function showWarningModal() {
        if (warningShown) return;
        warningShown = true;

        const modal = document.getElementById('session-timeout-modal');
        modal.classList.remove('hidden');

        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        countdownInterval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '/admin/logout';
            }
        }, 60000); // Update every minute
    }

    function extendSession() {
        // Send AJAX request to extend session
        fetch('/admin/extend-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                document.getElementById('session-timeout-modal').classList.add('hidden');
                warningShown = false;
                clearInterval(countdownInterval);
                // Reset the timer
                setTimeout(showWarningModal, logoutTime);
            }
        });
    }

    function logoutNow() {
        window.location.href = '/admin/logout';
    }

    // Event listeners
    document.getElementById('extend-session').addEventListener('click', extendSession);
    document.getElementById('logout-now').addEventListener('click', logoutNow);

    // Show warning after calculated time
    setTimeout(showWarningModal, logoutTime);
});
</script>