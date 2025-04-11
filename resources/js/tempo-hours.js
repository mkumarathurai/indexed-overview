document.addEventListener('livewire:initialized', () => {
    Livewire.on('tempo-hours-sync', (period) => {
        // Show loading indicator
        const refreshButton = document.querySelector('[wire\\:click="refresh"]');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        }

        // Make API request to trigger sync
        fetch('/api/tempo-hours/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ period })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page after a short delay to allow sync to complete
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Sync failed: ' + data.message);
                if (refreshButton) {
                    refreshButton.disabled = false;
                    refreshButton.innerHTML = '<i class="fas fa-sync"></i> Refresh';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while syncing data');
            if (refreshButton) {
                refreshButton.disabled = false;
                refreshButton.innerHTML = '<i class="fas fa-sync"></i> Refresh';
            }
        });
    });
}); 