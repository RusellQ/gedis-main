<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/js/app.js'])
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="assets/qr.png" type="image/x-icon">

    <title>@yield('title')</title> <!-- Si no se define un título específico en la vista, se usará "AGQROO" por defecto -->

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        /* Estilo hover para los elementos del menú */
        .navbar-nav .nav-item:hover {
            background-color: #e7e7ff;
            transition: background-color 0.3s ease;
        }

        .navbar-nav .nav-item:hover .nav-link span {
            color: white;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-item:hover .nav-link i {
            color: white;
            transition: color 0.3s ease;
        }

        /* Bloquear el hover en el ítem activo */
        .navbar-nav .nav-item.active:hover {
            background-color: #e7e7ff !important;
            cursor: default;
        }


        .nav-item.active>a.nav-link {
            background-color: #e7e7ff;
            /* Color de fondo para el elemento activo */
            color: #6969f4 !important;
            /* Color del texto */
        }

        .nav-item.active i {
            color: #6969f4 !important;
            /* Color del ícono */
        }

        .nav-item.active>a.nav-link {
            background-color: #e7e7ff;
            color: #6969f4 !important;
        }

        .nav-item.active>a.nav-link span {
            color: #6969f4 !important;
        }

        .nav-item.active>a.nav-link i {
            color: #6969f4 !important;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">

        @if(Auth::check() && Auth::user()->hasRole(['administrador', 'lector', 'editor','root']))
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon rotate-n-15">
                </div>
                <div class="sidebar-brand-logo">
                    <img src="assets/gedis.png" alt="Logo" style="max-width: 100%; height: auto; max-height: 60px;">
                </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">PANEL DE CONTROL</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">
    
            <!-- Sección: Expedientes -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExpedientes" aria-expanded="false" aria-controls="collapseExpedientes">
                    <i class="fas fa-folder-open" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">Expedientes</span>
                </a>
                <div id="collapseExpedientes" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if(Auth::user()->hasRole(['administrador', 'editor','root']))
                        <a class="collapse-item" href="/cargarexpedientes">Cargar expedientes</a>
                        <a class="collapse-item" href="/caratula-v2">Caratula de Expediente</a>
                        @endif
                        <a class="collapse-item" href="/consultar">Consultar caratulas</a>
                    </div>
                </div>
            </li>

            <!-- Sección: Archivo -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseArchivo" aria-expanded="false" aria-controls="collapseArchivo">
                    <i class="fas fa-archive" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">Archivo</span>
                </a>
                <div id="collapseArchivo" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/tramite">En Tramite</a>
                        <a class="collapse-item" href="/concentracion">En Concentracion</a>
                        <a class="collapse-item" href="/historico">En Historico</a>
                        @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item" href="/bajas">Bajas</a>
                        @endif
                    </div>
                </div>
            </li>

            <!-- Sección: Catálogo y Estructura -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCatalogo" aria-expanded="false" aria-controls="collapseCatalogo">
                    <i class="fas fa-sitemap" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">Catalogo y Estructura</span>
                </a>
                <div id="collapseCatalogo" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        {{-- @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item" href="/indexfondos">Fondos</a>
                        @endif --}}
                        @if(Auth::user()->hasRole(['administrador','root']))
                        <a class="collapse-item" href="/fondos">Crear Expedientes</a>
                        @endif
                        {{-- @if(Auth::user()->hasRole(['administrador', 'editor','root']))
                        <a class="collapse-item" href="/series">Crear Series</a>
                        @endif --}}
                        <a class="collapse-item" href="/cuadro">Cuadro General</a>
                        <a class="collapse-item" href="/catalogodisposicion">Catalogo Disposicion</a>
                        <a class="collapse-item" href="/catalogosecciones">Catalogo Secciones</a>
                    </div>
                </div>
            </li>

            <!-- Sección: Reportes -->
            <li class="nav-item">
                <a class="nav-link" href="/reportes">
                    <i class="fas fa-file-export" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">Reportes</span>
                </a>
            </li>

            <!-- Sección: Administración -->
            @if(Auth::user()->hasRole(['administrador','root']))
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdministracion" aria-expanded="false" aria-controls="collapseAdministracion">
                    <i class="fas fa-cogs" style="color: #6b7990;"></i>
                    <span style="color: #848B96;">Administracion</span>
                </a>
                <div id="collapseAdministracion" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item" href="/dependencias">Dependencias</a>
                        <a class="collapse-item" href="/catalogo-clasificacion">Catálogo de clasificación</a>
                        @endif
                        <a class="collapse-item" href="/departamentos">Departamentos</a>
                        <a class="collapse-item" href="/Createuser">Crear usuario</a>
                        <a class="collapse-item" href="/list-user">Listar usuarios</a>
                    </div>
                </div>
            </li>
            @endif








            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow position-relative">
                    <h1 class="navbar-brand position-absolute start-50 translate-middle-x text-center m-0">
                        @yield('page-title')
                    </h1>

                    <ul class="navbar-nav ml-auto">

                        @php
                        $totalAlertas = ($expedientesVencidos->count() ?? 0) + ($expedientesPorVencer->count() ?? 0);
                        @endphp

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                @if($totalAlertas > 0)
                                <span class="badge badge-danger badge-counter">{{ $totalAlertas }}</span>
                                @endif
                            </a>

                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">

                                {{-- Vencidos --}}
                                <h6 class="dropdown-header">Expedientes Vencidos</h6>
                                @forelse($expedientesVencidos as $expediente)
                                <a class="dropdown-item d-flex align-items-center"
                                    href="{{ url('/expedienteindividual?id=' . $expediente->id) }}">
                                    <div>
                                        <div class="small text-gray-500">Fecha cierre: {{ $expediente->fecha_cierre }}</div>
                                        <span class="font-weight-bold text-danger">{{ $expediente->nombre }}</span>
                                    </div>
                                </a>
                                @empty
                                <a class="dropdown-item text-center small text-gray-500">No hay expedientes vencidos</a>
                                @endforelse

                                <div class="dropdown-divider"></div>

                                {{-- Por vencer --}}
                                <h6 class="dropdown-header">Expedientes por vencer</h6>
                                @forelse($expedientesPorVencer as $expediente)
                                <a class="dropdown-item d-flex align-items-center"
                                    href="{{ url('/expedienteindividual?id=' . $expediente->id) }}">
                                    <div>
                                        <div class="small text-gray-500">Fecha cierre: {{ $expediente->fecha_cierre }}</div>
                                        <span class="font-weight-bold text-warning">{{ $expediente->nombre }}</span>
                                    </div>
                                </a>
                                @empty
                                <a class="dropdown-item text-center small text-gray-500">No hay expedientes próximos a vencer</a>
                                @endforelse
                            </div>
                        </li>


                        {{-- Usuario --}}
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    {{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}
                                </span>
                                <i class="fas fa-caret-down"></i>
                            </a>

                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->


                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- End of Main Content -->

            </div>
        </div>
        <!-- End of Content Wrapper -->

        @else

        <!-- Mostrar contenido alternativo para usuarios con rol "user" -->

        <div class="lock"></div>
        <div class="message">
            <h1>El acceso a esta pagina esta restingido</h1>
            <p>Por favor comunicate con el administrador si consideras que hay un error</p>
            <a href="http://localhost:8000" type="button" class="btn btn-danger">Salir</a>
        </div>

        <style>
            @import url('https://fonts.googleapis.com/css?family=Lato');

            * {
                position: relative;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Lato', sans-serif;
            }



            body {
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: linear-gradient(to bottom right, #EEE, #AAA);
            }

            h1 {
                margin: 40px 0 20px;
            }

            p {
                text-align: center;
            }

            .lock {
                border-radius: 5px;
                width: 65px;
                height: 45px;
                background-color: #333;
                animation: dip 1s;
                animation-delay: 1.5s;

            }

            .lock::before,
            .lock::after {
                content: '';
                position: absolute;
                border-left: 5px solid #333;
                height: 20px;
                width: 15px;
                left: calc(50% - 12.5px);
            }

            .lock::before {
                top: -30px;
                border: 5px solid #333;
                border-bottom-color: transparent;
                border-radius: 15px 15px 0 0;
                height: 30px;
                animation: lock 2s, spin 2s;
            }

            .lock::after {
                top: -10px;
                border-right: 5px solid transparent;
                animation: spin 2s;


            }

            .message {
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
            }




            @keyframes lock {
                0% {
                    top: -45px;
                }

                65% {
                    top: -45px;
                }

                100% {
                    top: -30px;
                }
            }

            @keyframes spin {
                0% {
                    transform: scaleX(-1);
                    left: calc(50% - 30px);
                }

                65% {
                    transform: scaleX(1);
                    left: calc(50% - 12.5px);
                }
            }

            @keyframes dip {
                0% {
                    transform: translateY(0px);
                }

                50% {
                    transform: translateY(10px);
                }

                100% {
                    transform: translateY(0px);
                }
            }
        </style>


        @endif
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">¿Estás listo para irte?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Seleccione "Cerrar sesión" a continuación si está listo para finalizar su sesión actual.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <form id="logout-form" action="{{ route('users.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button class="btn btn-primary" type="button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="assets/vendor/chart.js/Chart.min.js"></script>

</body>

</html>