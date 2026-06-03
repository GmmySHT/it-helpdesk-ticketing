<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Web RSIH')</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('assets/img/foto.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/foto.png') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/phosphor/regular/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/fontawesome.css') }}" />

    <!-- Vendor CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Optional Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plugins/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plugins/notyf.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.bubble.css" rel="stylesheet" />

    <!-- Main CSS -->
    <link href="{{ asset('assets/css/admin_style.css') }}" rel="stylesheet">

    <!-- Stack Styles - HARUS ADA untuk menerima CSS dari @push('styles') -->
    @stack('styles')

    <style>
        .preview-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .preview-content table,
        .preview-content th,
        .preview-content td {
            border: 1px solid #000;
        }

        .preview-content th,
        .preview-content td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body data-pc-theme="light">
    <!-- Loader -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <!-- SweetAlert -->
    @include('sweetalert::alert')

    <!-- Header -->
    @include('layouts.header')

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main Content Wrapper -->
    <div class="main-content-wrapper">
        <!-- Main Content -->
        <main id="main" class="main">
            @yield('content')
        </main>

        <!-- Footer Premium -->
        @include('layouts.footer')
    </div>

    <!-- Back to top -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/admin.js') }}"></script>

    <!-- Optional Plugins JS -->
    <script src="{{ asset('assets/admin/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugins/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugins/notyf.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-table-better@1/dist/quill-table-better.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    <!-- Dashboard Script -->
    <script src="{{ asset('assets/admin/js/pages/dashboard-analytics.js') }}"></script>

    <!-- Stack Scripts - HARUS ADA untuk menerima JS dari @push('scripts') -->
    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // DataTables
            if ($('.datatable').length) {
                $('.datatable').DataTable();
            }

            // SweetAlert Hapus
            document.querySelectorAll(".delete-confirm").forEach(button => {
                button.addEventListener("click", function(event) {
                    event.preventDefault();
                    let form = this.closest("form");
                    Swal.fire({
                        title: "Yakin ingin menghapus data ini?",
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, hapus!"
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            // SweetAlert Flash
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            // Highlight.js
            if (typeof hljs !== 'undefined') hljs.highlightAll();

            // Quill Editor Init
            document.querySelectorAll('.quill-editor').forEach(function(editorEl) {
                const quill = new Quill(editorEl, {
                    theme: 'snow',
                    modules: {
                        syntax: true,
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ],
                        table: true
                    }
                });
            });

        });
    </script>

    @stack('js')
</body>

</html>
