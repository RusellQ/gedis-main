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
        /* ============================================================
           Paleta corporativa (misma que Expedientes y Dashboard)
           7421C #440412 · 1945C #AB0A3D · Black 7C #3D3935
           125C #B68400 · 401C #B0ABA1
           ============================================================ */
        :root {
            --bg: #f7f5f2;
            --surface: #ffffff;
            --ink: #3D3935;
            --ink-soft: #78736a;
            --line: #e4e0d8;
            --brand: #AB0A3D;
            --brand-dark: #440412;
            --brand-soft: #f7e5eb;
            --gold: #B68400;
            --gold-soft: #faf1dd;
            --stone: #B0ABA1;
        }

        body {
            background-color: var(--bg);
        }

        /* ---------- Sidebar ---------- */
        #accordionSidebar.sidebar {
            background: linear-gradient(180deg, var(--brand-dark) 0%, var(--brand) 100%) !important;
            overflow-x: hidden;
        }

        @media (min-width: 768px) {
            #accordionSidebar.sidebar {
                width: 16rem !important;
            }
        }

        /* Logo rojo sobre placa clara: el PNG es maroon y se pierde en el fondo corporativo */
        #accordionSidebar .sidebar-brand {
            height: auto;
            padding: 1rem 0.85rem 0.85rem;
        }

        #accordionSidebar .sidebar-brand-logo {
            background: #fff;
            border-radius: 0.55rem;
            padding: 0.45rem 0.6rem;
            width: 100%;
            line-height: 0;
        }

        #accordionSidebar .sidebar-brand-logo img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 52px;
            object-fit: contain;
        }

        #accordionSidebar .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            margin: 0.5rem 1rem;
        }

        /* Ítems: width auto para no sumar el 14rem de sb-admin-2 + márgenes */
        #accordionSidebar .nav-item {
            position: relative;
            border-radius: 0;
        }

        #accordionSidebar .nav-item > a.nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0.85rem 0.2rem;
            border-radius: 0.5rem;
            margin: 0.15rem 0.45rem;
            width: auto !important;
            box-sizing: border-box;
            transition: background-color 0.18s ease;
        }

        #accordionSidebar .nav-item > a.nav-link i {
            flex: 0 0 auto;
            width: 1.1rem;
            text-align: center;
            margin-right: 0;
            color: rgba(255, 255, 255, 0.7) !important;
            transition: color 0.18s ease;
        }

        #accordionSidebar .nav-item > a.nav-link span {
            display: none;
            color: rgba(255, 255, 255, 0.92) !important;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.18s ease;
        }

        #accordionSidebar .nav-item > a.nav-link[data-toggle="collapse"]::after {
            display: none;
        }

        @media (min-width: 768px) {
            #accordionSidebar:not(.toggled) .nav-item > a.nav-link {
                flex-direction: row;
                justify-content: flex-start;
                gap: 0.6rem;
                padding: 0.75rem 0.85rem;
                margin: 0.12rem 0.65rem;
            }

            #accordionSidebar:not(.toggled) .nav-item > a.nav-link i {
                flex: 0 0 1.1rem;
            }

            #accordionSidebar:not(.toggled) .nav-item > a.nav-link span {
                display: block;
                flex: 1 1 auto;
                min-width: 0;
            }

            #accordionSidebar:not(.toggled) .nav-item > a.nav-link[data-toggle="collapse"]::after {
                display: inline-block;
                float: none;
                margin-left: auto;
                flex: 0 0 1rem;
            }

            #accordionSidebar:not(.toggled) .nav-item .collapse,
            #accordionSidebar:not(.toggled) .nav-item .collapsing {
                margin: 0 !important;
            }
        }

        #accordionSidebar .nav-item:hover > a.nav-link {
            background-color: rgba(255, 255, 255, 0.1);
        }

        #accordionSidebar .nav-item:hover > a.nav-link i,
        #accordionSidebar .nav-item:hover > a.nav-link span {
            color: #ffffff !important;
        }

        #accordionSidebar .nav-item.active {
            background-color: transparent;
        }

        #accordionSidebar .nav-item.active > a.nav-link {
            background-color: rgba(255, 255, 255, 0.14) !important;
            position: relative;
        }

        #accordionSidebar .nav-item.active > a.nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 3px;
            border-radius: 0 2px 2px 0;
            background: var(--gold);
        }

        #accordionSidebar .nav-item.active > a.nav-link i,
        #accordionSidebar .nav-item.active > a.nav-link span,
        #accordionSidebar .nav-item.active i {
            color: #ffffff !important;
        }

        #accordionSidebar .nav-item.active:hover {
            background-color: transparent !important;
            cursor: default;
        }

        /* Submenús: caben dentro del sidebar, el texto se envuelve */
        #accordionSidebar .collapse-inner,
        #accordionSidebar .collapsing .collapse-inner {
            border-radius: 0.55rem !important;
            box-shadow: none !important;
            margin: 0 0.65rem 0.45rem !important;
            padding: 0.35rem !important;
            width: auto !important;
            max-width: 100%;
        }

        #accordionSidebar .collapse-item {
            border-radius: 0.4rem;
            padding: 0.5rem 0.7rem;
            margin: 0.08rem 0;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ink-soft);
            white-space: normal !important;
            overflow-wrap: anywhere;
            line-height: 1.3;
            transition: background-color 0.15s, color 0.15s;
        }

        #accordionSidebar .collapse-item:hover,
        #accordionSidebar .collapse-item:focus {
            background-color: var(--brand-soft);
            color: var(--brand-dark);
        }

        #accordionSidebar .collapse-item.active {
            background-color: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 700;
        }

        #accordionSidebar #sidebarToggle {
            background-color: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        #accordionSidebar #sidebarToggle:hover {
            background-color: rgba(255, 255, 255, 0.24);
        }

        /* Móvil / sidebar estrecho: submenús vuelan a la derecha */
        @media (max-width: 767.98px) {
            #accordionSidebar.sidebar {
                overflow: visible;
            }

            #accordionSidebar .sidebar-brand {
                padding: 0.85rem 0.55rem;
            }

            #accordionSidebar .sidebar-brand-logo {
                padding: 0.35rem;
            }

            #accordionSidebar .sidebar-brand-logo img {
                max-height: 28px;
            }

            #accordionSidebar .collapse-inner {
                min-width: 14rem;
                width: max-content !important;
                margin: 0.15rem 0 0.15rem 0.4rem !important;
                box-shadow: 0 8px 22px rgba(20, 10, 15, 0.22) !important;
            }

            #accordionSidebar .collapse-item {
                white-space: nowrap !important;
                overflow-wrap: normal;
            }
        }

        /* Retráctil: solo iconos; submenús salen a la derecha sin recortar texto */
        @media (min-width: 768px) {
            #accordionSidebar.sidebar.toggled {
                width: 6.5rem !important;
                overflow: visible;
            }

            #accordionSidebar.toggled .sidebar-brand {
                padding: 0.85rem 0.55rem;
            }

            #accordionSidebar.toggled .sidebar-brand-logo {
                padding: 0.35rem;
            }

            #accordionSidebar.toggled .sidebar-brand-logo img {
                max-height: 28px;
            }

            #accordionSidebar.toggled .nav-item > a.nav-link {
                flex-direction: column;
                justify-content: center;
                gap: 0;
                margin: 0.15rem 0.45rem;
                padding: 0.85rem 0.2rem;
            }

            #accordionSidebar.toggled .nav-item > a.nav-link span,
            #accordionSidebar.toggled .nav-item > a.nav-link[data-toggle="collapse"]::after {
                display: none;
            }

            #accordionSidebar.toggled .nav-item > a.nav-link i {
                margin-right: 0;
                font-size: 1rem;
            }

            #accordionSidebar.toggled .nav-item .collapse {
                position: absolute;
                left: 6.5rem;
                top: 0;
                margin: 0;
                z-index: 20;
            }

            #accordionSidebar.toggled .collapse-inner {
                min-width: 14rem;
                width: max-content !important;
                max-width: 18rem;
                margin: 0.15rem 0 0.15rem 0.4rem !important;
                box-shadow: 0 8px 22px rgba(20, 10, 15, 0.22) !important;
            }

            #accordionSidebar.toggled .collapse-item {
                white-space: nowrap !important;
                overflow-wrap: normal;
            }
        }

        /* ---------- Topbar ---------- */
        .topbar {
            border-bottom: 1px solid var(--line);
            box-shadow: 0 1px 2px rgba(20, 30, 60, 0.04) !important;
        }

        .topbar .navbar-brand {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink) !important;
            letter-spacing: -0.01em;
        }

        .topbar .nav-link {
            color: var(--ink-soft) !important;
        }

        .topbar #alertsDropdown i,
        .topbar #userDropdown i {
            color: var(--ink-soft) !important;
        }

        .badge-counter {
            background-color: var(--brand) !important;
        }

        .dropdown-list .dropdown-header {
            background-color: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .dropdown-menu {
            border: 1px solid var(--line);
            border-radius: 0.7rem;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--brand-soft);
        }

        /* ---------- Botones globales ---------- */
        .btn-primary {
            background-color: var(--brand) !important;
            border-color: var(--brand) !important;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--brand-dark) !important;
            border-color: var(--brand-dark) !important;
        }

        .btn-secondary {
            background-color: #fff !important;
            border-color: var(--line) !important;
            color: var(--ink) !important;
        }

        .btn-secondary:hover {
            background-color: #f5f7fb !important;
            border-color: var(--brand) !important;
        }

        /* Scroll to top */
        .scroll-to-top {
            background-color: var(--brand);
        }

        .scroll-to-top:hover {
            background-color: var(--brand-dark);
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
                <div class="sidebar-brand-logo">
                    <img src="assets/gedis.png" alt="Sistema de gestión documental automatizado">
                </div>
            </a>

            @php
                $navIs = fn (...$patterns) => request()->is(...$patterns);
                $navExpedientes = $navIs('cargarexpedientes', 'expedientes', 'capturar', 'caratula-v2', 'consultar', 'buscar', 'expedienteindividual');
                $navArchivo = $navIs('tramite', 'concentracion', 'historico', 'vencidos', 'expedientes/vencidos*', 'bajas', 'indexbaja');
                $navCatalogo = $navIs('fondos', 'indexfondos', 'series', 'cuadro', 'cuadrogeneral*', 'catalogodisposicion*', 'catalogosecciones*');
                $navReportes = $navIs('reportes', 'reportes/*');
                $navAdmin = $navIs('dependencias', 'indexdependencias', 'catalogo-clasificacion', 'departamentos', 'indexdepartamentos', 'Createuser', 'list-user', 'list-users');
            @endphp

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ $navIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="/dashboard" title="Panel de control">
                    <i class="fas fa-home"></i>
                    <span>Panel de control</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Sección: Expedientes -->
            <li class="nav-item {{ $navExpedientes ? 'active' : '' }}">
                <a class="nav-link {{ $navExpedientes ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseExpedientes" aria-expanded="{{ $navExpedientes ? 'true' : 'false' }}" aria-controls="collapseExpedientes" title="Expedientes">
                    <i class="fas fa-folder-open"></i>
                    <span>Expedientes</span>
                </a>
                <div id="collapseExpedientes" class="collapse {{ $navExpedientes ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if(Auth::user()->hasRole(['administrador', 'editor','root']))
                        <a class="collapse-item {{ $navIs('cargarexpedientes', 'expedientes', 'capturar') ? 'active' : '' }}" href="/cargarexpedientes">Cargar expedientes</a>
                        <a class="collapse-item {{ $navIs('caratula-v2') ? 'active' : '' }}" href="/caratula-v2">Carátula de expediente</a>
                        @endif
                        <a class="collapse-item {{ $navIs('consultar', 'buscar', 'expedienteindividual') ? 'active' : '' }}" href="/consultar">Consultar caratulas</a>
                    </div>
                </div>
            </li>

            <!-- Sección: Archivo -->
            <li class="nav-item {{ $navArchivo ? 'active' : '' }}">
                <a class="nav-link {{ $navArchivo ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseArchivo" aria-expanded="{{ $navArchivo ? 'true' : 'false' }}" aria-controls="collapseArchivo" title="Archivo">
                    <i class="fas fa-archive"></i>
                    <span>Archivo</span>
                </a>
                <div id="collapseArchivo" class="collapse {{ $navArchivo ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ $navIs('tramite') ? 'active' : '' }}" href="/tramite">En Tramite</a>
                        <a class="collapse-item {{ $navIs('concentracion') ? 'active' : '' }}" href="/concentracion">En Concentracion</a>
                        <a class="collapse-item {{ $navIs('historico') ? 'active' : '' }}" href="/historico">En Historico</a>
                        @if(Auth::user()->hasRole(['administrador', 'root']))
                        <a class="collapse-item {{ $navIs('vencidos', 'expedientes/vencidos*') ? 'active' : '' }}" href="/vencidos">Expedientes Vencidos</a>
                        @endif
                        @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item {{ $navIs('bajas', 'indexbaja') ? 'active' : '' }}" href="/bajas">Bajas</a>
                        @endif
                    </div>
                </div>
            </li>

            <!-- Sección: Catálogo y Estructura -->
            <li class="nav-item {{ $navCatalogo ? 'active' : '' }}">
                <a class="nav-link {{ $navCatalogo ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseCatalogo" aria-expanded="{{ $navCatalogo ? 'true' : 'false' }}" aria-controls="collapseCatalogo" title="Catálogo y estructura">
                    <i class="fas fa-sitemap"></i>
                    <span>Catálogo y estructura</span>
                </a>
                <div id="collapseCatalogo" class="collapse {{ $navCatalogo ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        {{-- @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item" href="/indexfondos">Fondos</a>
                        @endif --}}
                        @if(Auth::user()->hasRole(['administrador','root']))
                        <a class="collapse-item {{ $navIs('fondos', 'indexfondos') ? 'active' : '' }}" href="/fondos">Crear Expedientes</a>
                        @endif
                        {{-- @if(Auth::user()->hasRole(['administrador', 'editor','root']))
                        <a class="collapse-item" href="/series">Crear Series</a>
                        @endif --}}
                        <a class="collapse-item {{ $navIs('cuadro', 'cuadrogeneral*') ? 'active' : '' }}" href="/cuadro">Cuadro General</a>
                        <a class="collapse-item {{ $navIs('catalogodisposicion*') ? 'active' : '' }}" href="/catalogodisposicion">Catalogo Disposicion</a>
                        <a class="collapse-item {{ $navIs('catalogosecciones*') ? 'active' : '' }}" href="/catalogosecciones">Catalogo Secciones</a>
                    </div>
                </div>
            </li>

            <!-- Sección: Reportes -->
            <li class="nav-item {{ $navReportes ? 'active' : '' }}">
                <a class="nav-link" href="/reportes" title="Reportes">
                    <i class="fas fa-file-export"></i>
                    <span>Reportes</span>
                </a>
            </li>

            <!-- Sección: Administración -->
            @if(Auth::user()->hasRole(['administrador','root']))
            <li class="nav-item {{ $navAdmin ? 'active' : '' }}">
                <a class="nav-link {{ $navAdmin ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseAdministracion" aria-expanded="{{ $navAdmin ? 'true' : 'false' }}" aria-controls="collapseAdministracion" title="Administración">
                    <i class="fas fa-cogs"></i>
                    <span>Administración</span>
                </a>
                <div id="collapseAdministracion" class="collapse {{ $navAdmin ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if(Auth::user()->hasRole(['root']))
                        <a class="collapse-item {{ $navIs('dependencias', 'indexdependencias') ? 'active' : '' }}" href="/dependencias">Dependencias</a>
                        <a class="collapse-item {{ $navIs('catalogo-clasificacion') ? 'active' : '' }}" href="/catalogo-clasificacion">Catálogo de clasificación</a>
                        @endif
                        <a class="collapse-item {{ $navIs('departamentos', 'indexdepartamentos') ? 'active' : '' }}" href="/departamentos">Departamentos</a>
                        <a class="collapse-item {{ $navIs('Createuser') ? 'active' : '' }}" href="/Createuser">Crear usuario</a>
                        <a class="collapse-item {{ $navIs('list-user', 'list-users') ? 'active' : '' }}" href="/list-user">Listar usuarios</a>
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
                background: linear-gradient(135deg, #f7f5f2, #e4e0d8);
            }

            h1 {
                margin: 40px 0 20px;
                color: #3D3935;
                font-size: 1.4rem;
            }

            p {
                text-align: center;
                color: #78736a;
            }

            .lock {
                border-radius: 5px;
                width: 65px;
                height: 45px;
                background-color: #440412;
                animation: dip 1s;
                animation-delay: 1.5s;

            }

            .lock::before,
            .lock::after {
                content: '';
                position: absolute;
                border-left: 5px solid #440412;
                height: 20px;
                width: 15px;
                left: calc(50% - 12.5px);
            }

            .lock::before {
                top: -30px;
                border: 5px solid #440412;
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

            .message .btn-danger {
                margin-top: 1rem;
                background-color: #AB0A3D;
                border-color: #AB0A3D;
                font-weight: 600;
            }

            .message .btn-danger:hover {
                background-color: #440412;
                border-color: #440412;
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