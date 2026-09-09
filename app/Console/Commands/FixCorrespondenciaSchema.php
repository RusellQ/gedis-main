<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repara de forma idempotente las tablas del módulo de Correspondencia
 * (creadas originalmente por database/sql/004-correspondencias.sql), agregando
 * cualquier columna, índice o llave foránea que falte respecto al esquema
 * esperado por los modelos de Eloquent.
 */
class FixCorrespondenciaSchema extends Command
{
    protected $signature = 'gedis:fix-correspondencia-schema';

    protected $description = 'Repara el esquema de las tablas de Correspondencia agregando columnas/índices/FKs faltantes';

    public function handle(): int
    {
        if (!Schema::hasTable('correspondencias')) {
            $this->error('La tabla `correspondencias` no existe. Ejecuta primero database/sql/004-correspondencias.sql');
            return self::FAILURE;
        }

        $this->fixCorrespondencias();
        $this->fixCorrespondenciaFirmas();
        $this->fixCorrespondenciaAnexos();
        $this->fixCorrespondenciaAuditorias();

        $this->info('Esquema de correspondencia verificado/reparado correctamente.');

        return self::SUCCESS;
    }

    private function fixCorrespondencias(): void
    {
        Schema::table('correspondencias', function (Blueprint $table) {
            $this->addColumnIfMissing('correspondencias', 'id_expediente', fn () => $table->unsignedBigInteger('id_expediente')->after('id'));
            $this->addColumnIfMissing('correspondencias', 'numero_consecutivo', fn () => $table->integer('numero_consecutivo')->default(1));
            $this->addColumnIfMissing('correspondencias', 'id_usuario_autor', fn () => $table->unsignedBigInteger('id_usuario_autor'));
            $this->addColumnIfMissing('correspondencias', 'remitente', fn () => $table->string('remitente')->nullable());
            $this->addColumnIfMissing('correspondencias', 'destinatario', fn () => $table->string('destinatario')->nullable());
            $this->addColumnIfMissing('correspondencias', 'asunto', fn () => $table->text('asunto'));
            $this->addColumnIfMissing('correspondencias', 'contenido', fn () => $table->longText('contenido'));
            $this->addColumnIfMissing('correspondencias', 'clasificacion', fn () => $table->enum('clasificacion', ['publica', 'reservada', 'confidencial'])->default('publica'));
            $this->addColumnIfMissing('correspondencias', 'estado', fn () => $table->enum('estado', ['borrador', 'firmado', 'cancelado'])->default('borrador'));
            $this->addColumnIfMissing('correspondencias', 'fecha_creacion', fn () => $table->timestamp('fecha_creacion')->nullable()->useCurrent());
            $this->addColumnIfMissing('correspondencias', 'fecha_modificacion', fn () => $table->timestamp('fecha_modificacion')->nullable()->useCurrent()->useCurrentOnUpdate());
            $this->addColumnIfMissing('correspondencias', 'deleted_at', fn () => $table->softDeletes());
        });

        $this->addIndexIfMissing('correspondencias', 'idx_expediente', ['id_expediente']);
        $this->addIndexIfMissing('correspondencias', 'idx_usuario_autor', ['id_usuario_autor']);
        $this->addIndexIfMissing('correspondencias', 'idx_estado', ['estado']);
        $this->addForeignKeyIfMissing('correspondencias', 'fk_correspondencias_expediente', 'id_expediente', 'expedientes', 'id', 'cascade');
        $this->addForeignKeyIfMissing('correspondencias', 'fk_correspondencias_usuario', 'id_usuario_autor', 'users', 'id', 'restrict');
    }

    private function fixCorrespondenciaFirmas(): void
    {
        if (!Schema::hasTable('correspondencia_firmas')) {
            Schema::create('correspondencia_firmas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_correspondencia');
                $table->unsignedBigInteger('id_usuario_firmante');
                $table->text('certificado_sat')->nullable();
                $table->timestamp('timestamp_firma')->nullable();
                $table->enum('estado_firma', ['pendiente', 'exitosa', 'rechazada'])->default('pendiente');
                $table->json('respuesta_sat')->nullable();
                $table->timestamp('fecha_intento')->nullable()->useCurrent();
                $table->unique('id_correspondencia', 'uk_correspondencia_ultima_firma');
            });
        }

        $this->addForeignKeyIfMissing('correspondencia_firmas', 'fk_correspondencia_firmas_correspondencia', 'id_correspondencia', 'correspondencias', 'id', 'cascade');
        $this->addForeignKeyIfMissing('correspondencia_firmas', 'fk_correspondencia_firmas_usuario', 'id_usuario_firmante', 'users', 'id', 'restrict');
    }

    private function fixCorrespondenciaAnexos(): void
    {
        if (!Schema::hasTable('correspondencia_anexos')) {
            Schema::create('correspondencia_anexos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_correspondencia');
                $table->unsignedBigInteger('id_unidad_documental');
                $table->integer('orden')->default(0);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->unique(['id_correspondencia', 'id_unidad_documental'], 'uk_correspondencia_anexo');
            });
        }

        $this->addForeignKeyIfMissing('correspondencia_anexos', 'fk_correspondencia_anexos_correspondencia', 'id_correspondencia', 'correspondencias', 'id', 'cascade');
        $this->addForeignKeyIfMissing('correspondencia_anexos', 'fk_correspondencia_anexos_unidad', 'id_unidad_documental', 'unidades_documentales', 'id', 'cascade');
    }

    private function fixCorrespondenciaAuditorias(): void
    {
        if (!Schema::hasTable('correspondencia_auditorias')) {
            Schema::create('correspondencia_auditorias', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_correspondencia');
                $table->unsignedBigInteger('id_usuario');
                $table->string('accion', 50);
                $table->text('detalles')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
            });
        }

        $this->addForeignKeyIfMissing('correspondencia_auditorias', 'fk_correspondencia_auditorias_correspondencia', 'id_correspondencia', 'correspondencias', 'id', 'cascade');
        $this->addForeignKeyIfMissing('correspondencia_auditorias', 'fk_correspondencia_auditorias_usuario', 'id_usuario', 'users', 'id', 'restrict');
    }

    private function addColumnIfMissing(string $table, string $column, \Closure $definer): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        $definer();
        $this->line("  + columna `{$column}` agregada a `{$table}`");
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
            $this->line("  + índice `{$indexName}` agregado a `{$table}`");
        } catch (\Throwable $e) {
            $this->warn("  ! no se pudo agregar índice `{$indexName}`: {$e->getMessage()}");
        }
    }

    private function addForeignKeyIfMissing(string $table, string $fkName, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
        $exists = DB::table('information_schema.table_constraints')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $fkName)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $refColumn, $onDelete, $fkName) {
                $blueprint->foreign($column, $fkName)
                    ->references($refColumn)->on($refTable)
                    ->onDelete($onDelete);
            });
            $this->line("  + FK `{$fkName}` agregada a `{$table}`");
        } catch (\Throwable $e) {
            $this->warn("  ! no se pudo agregar la llave foránea `{$fkName}`: {$e->getMessage()}");
        }
    }
}
