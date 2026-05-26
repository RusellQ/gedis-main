<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\CatalogoArchivisticoController;
use App\Http\Controllers\SerieController;
use App\Http\Controllers\FondoController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\UnidadDocumentalController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SubfondoController;
use App\Http\Controllers\SubserieController;
use App\Http\Controllers\CuadroGeneralController;
use App\Http\Controllers\CatalogoDocumentalController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DepartamentoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//VISTAS 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/capturar', function () {
    return view('administrativas.capturar');
});

Route::get('/consultar', function () {
    return view('administrativas.consultar');
});

Route::get('/buscar', function () {
    return view('administrativas.buscar');
});

Route::get('/fondos', function () {
    return view('administrativas.fondos');
});

Route::get('/series', function () {
    return view('administrativas.series');
});

Route::get('/caratula-v2', function () {
    return view('administrativas.caratula_v2');
});

Route::get('/expedientes', [ExpedienteController::class, 'index']);

Route::get('/cargarexpedientes', function () {
    return view('administrativas.expedientes');
});

Route::get('/estadisticas', function () {
    return view('administrativas.estadisticas');
});

Route::get('/inventario', function () {
    return view('administrativas.inventario');
});

Route::get('/historico', function () {
    return view('administrativas.historico');
});

Route::get('/tramite', function () {
    return view('administrativas.tramite');
});

Route::get('/concentracion', function () {
    return view('administrativas.concentracion');
});

Route::get('/reportes', function () {
    return view('administrativas.reportes');
});

Route::get('/bajas', function () {
    return view('administrativas.bajas');
});

Route::get('/cuadro', function () {
    return view('administrativas.cuadro');
});

Route::get('/catalogodisposicion', function () {
    return view('administrativas.catalogodisposicion');
});

Route::get('/dependencias', function () {
    return view('administrativas.dependencias');
});

Route::get('/departamentos', function () {
    return view('administrativas.departamentos');
});

Route::get('/indexfondos', function () {
    return view('administrativas.Indexfondos');
});


//METODOS

Route::post('/creardependencia', [DependenciaController::class, 'store']);
Route::get('/indexdependencias', [DependenciaController::class, 'index']);

Route::get('/indexdepartamentos', [DepartamentoController::class, 'index']);
Route::post('/creardepartamento', [DepartamentoController::class, 'store']);


Route::get('/cuadrogeneral/vista-previa', [CuadroGeneralController::class, 'vistaPrevia']);
Route::get('/cuadrogeneral/pdf', [CuadroGeneralController::class, 'generarPDF']);

Route::get('/catalogodisposicion/vista-previa', [CatalogoDocumentalController::class, 'catalogoDisposicion']);
Route::get('/catalogodisposicion/pdf', [CatalogoDocumentalController::class, 'generarPDFDisposicion']);

Route::get('/catalogosecciones', function () {
    return view('administrativas.catalogosecciones');
});
Route::get('/catalogosecciones/data', [CatalogoDocumentalController::class, 'dataSecciones']);
Route::get('/catalogosecciones/vista-previa', [CatalogoDocumentalController::class, 'vistaPreviaSecciones']);
Route::get('/catalogosecciones/pdf', [CatalogoDocumentalController::class, 'generarPDFSecciones']);

Route::get('/reportes/filtrar', [ReporteController::class, 'filtrar']);
Route::get('/reportes/pdf', [ReporteController::class, 'generarPDF']);

Route::get('/catalogo/fondos', [CatalogoArchivisticoController::class, 'obtenerFondos']);
Route::get('/catalogo/secciones', [CatalogoArchivisticoController::class, 'obtenerSecciones']);
Route::get('/catalogo/series', [CatalogoArchivisticoController::class, 'obtenerSeries']);
Route::get('/catalogo/expedientes', [CatalogoArchivisticoController::class, 'obtenerExpedientes']);

Route::post('/guardarfondos', [FondoController::class, 'store']);
Route::get('/mostrarfondos', [FondoController::class, 'index']);
Route::get('/allFondos', [FondoController::class, 'allFondos']);
Route::get('/fondosPorDependencia', [FondoController::class, 'fondosPorDependencia']);

Route::get('/fondos/{id}/secciones', [FondoController::class, 'secciones']);
Route::get('/subfondos/{id}/secciones', [SubfondoController::class, 'secciones']);
Route::get('/fondos/{id}/subfondos', [FondoController::class, 'subfondos']);
Route::post('/guardarsubfondo', [SubfondoController::class, 'store']);
Route::post('/guardarSubserie', [SubserieController::class, 'store']);
Route::get('/series/{id}/subseries', [SubserieController::class, 'index']);
Route::get('/subseries/{id}/expedientes', [SubserieController::class, 'expedientes']);
Route::post('/series', [SerieController::class, 'store']);
Route::put('/editarexpedientes/{id}', [ExpedienteController::class, 'actualizar']);
Route::get('/expedienteindividual', function () {
    return view('administrativas.individual');
});
Route::delete('/bajaexpediente/{id}', [ExpedienteController::class, 'darDeBaja'])
    ->name('expediente.baja')
    ->middleware('auth');
Route::get('/expedienteid/{id}', [ExpedienteController::class, 'getExpedienteById']);

// Batch retrieval de expedientes por IDs (usado por Carátula v2)
Route::post('/expedientes/batch', [ExpedienteController::class, 'batchByIds']);

Route::put('/cambiarEstado/{id}', [ExpedienteController::class, 'cambiarEstado']);
Route::get('/indexbaja', [ExpedienteController::class, 'indexbaja']);


Route::get('/mostrarseries', [SerieController::class, 'index']);
Route::post('/secciones', [SeccionController::class, 'store']);
Route::post('/fondos/{fondo}/secciones', [SeccionController::class, 'store']);
Route::get('/secciones/{id}/series', [SerieController::class, 'getSeriesBySeccion']);

Route::post('/guardarserie', [SerieController::class, 'store']);

Route::get('/series/{id}/expedientes', [ExpedienteController::class, 'porSerie']);
Route::get('/subseries/{id}/expedientes', [ExpedienteController::class, 'porSubserie']);

Route::post('/guardarexpediente', [ExpedienteController::class, 'store']);

Route::post('/unidades-documentales', [UnidadDocumentalController::class, 'store']);


Route::get('/catalogos/fondos', [ReporteController::class, 'fondos']);
Route::get('/catalogos/subfondos', [ReporteController::class, 'subfondos']);
Route::get('/catalogos/secciones', [ReporteController::class, 'secciones']);
Route::get('/catalogos/series', [ReporteController::class, 'series']);
Route::get('/catalogos/subseries', [ReporteController::class, 'subseries']);

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/dashboard', [UsersController::class, 'dashboard'])->name('dashboard')->middleware('auth');

Route::get('/Createuser', function () {
    return view('administrativas.createuser');
});

Route::get('/list-user', function () {
    return view('administrativas.listuser');
});

Route::get('/obtenerstats', [EstadisticasController::class, 'obtenerEstadisticas']);

Route::get('/obtenerinventario', [EstadisticasController::class, 'mostrarInventario']);

Route::get('/exportar-inventario', [EstadisticasController::class, 'exportarInventario']);


Route::get('/currentuser', [UsersController::class, 'CurrentUser']);

Route::post('/buscar', [DocumentoController::class, 'buscar']);


//RUTAS PARA USUARIOS ADMINISTRATIVOS
Route::post('/users', [UsersController::class, 'store'])->name('users.store');
Route::post('/login', [UsersController::class, 'login'])->name('users.login');
Route::post('/logout', [UsersController::class, 'logout'])->name('users.logout');

Route::get('/list-users', [UsersController::class, 'listUsers'])->name('users.list');
Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');



Route::get('/documentos/{documentId}', [DocumentoController::class, 'getDocumentById']);
Route::get('/documentos', [DocumentoController::class, 'getAllDocuments']);
Route::get('/documentosDesechados', [DocumentoController::class, 'getAllDocumentsDesechados']);
Route::post('/documentos', [DocumentoController::class, 'storeDocument']);
Route::delete('/documentos/{documentId}', [DocumentoController::class, 'deleteDocument']);
Route::post('/documentos/update/{documentId}', [DocumentoController::class, 'updateDocument']);
Route::get('/ultimos-documentos', [DocumentoController::class, 'obtenerUltimosDocumentos']);
