</div> <!-- End of app-content -->
</div> <!-- End of app-container -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<!-- Global Scripts -->
<script>
$(document).ready(function() {
    // Initialize all datatables
    $('.datatable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.2/i18n/es-ES.json'
        },
        responsive: true
    });
    
    // Initialize all tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Toggle sidebar on mobile
    $('#sidebar-toggle').on('click', function() {
        $('.app-container').toggleClass('sidebar-mobile-open');
    });
});

// Flash messages auto hide
window.setTimeout(function() {
    $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
        $(this).remove();
    });
}, 4000);
</script>

<!-- Page specific scripts -->
<?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
<?php endif; ?>

</body>
</html>