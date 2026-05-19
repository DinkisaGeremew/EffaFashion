</div><!-- /admin-content -->
</div><!-- /admin-main -->
</div><!-- /admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Sidebar toggle for mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('open');
});

// Confirm delete
document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href || this.dataset.href;
        Swal.fire({
            title: 'Are you sure?',
            text: this.dataset.msg || 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then(result => {
            if (result.isConfirmed) window.location.href = href;
        });
    });
});

// Auto-dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity 0.5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);
</script>
</body>
</html>
