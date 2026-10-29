<?php
declare(strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class OrdenesTrabajoValidator {

    /** Campos que se pueden modificar con PUT (id y fecha_ingreso los genera MySQL). */
    private const CAMPOS_ACTUALIZABLES = ['placa_vehiculo', 'propietario', 'descripcion_falla', 'costo_estimado', 'estado'];

    private function reglas(): array {
        return [
            'placa_vehiculo' => [
                'regla' => v::stringType()->notBlank()->length(v::between(1, 10))->regex('/^[A-Za-z0-9-]{1,10}$/'),
                'mensaje' => 'La placa del vehículo es obligatoria, máximo 10 caracteres (solo letras, números y guiones)',
            ],
            'propietario' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(3, 100))
                    ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóúÜü ]{3,100}$/u'),
                'mensaje' => 'El propietario es obligatorio, debe tener entre 3 y 100 caracteres y solo contener letras y espacios',
            ],
            'descripcion_falla' => [
                'regla' => v::stringType()->notBlank()->length(v::between(5, 65535)),
                'mensaje' => 'La descripción de la falla es obligatoria y debe tener al menos 5 caracteres',
            ],
            'costo_estimado' => [
                'regla' => v::numericVal()->between(0, 99999999.99),
                'mensaje' => 'El costo estimado es obligatorio y debe ser un número entre 0 y 99999999.99',
            ],
            'estado' => [
                'regla' => v::stringType()->in(['Pendiente', 'En Proceso', 'Finalizado', 'Entregado']),
                'mensaje' => 'El estado debe ser uno de: Pendiente, En Proceso, Finalizado, Entregado',
            ],
        ];
    }

    /** Validación completa para CREATE (POST). El estado es opcional (MySQL asigna 'Pendiente'). */
    public function validate(array $data): array {
        return $this->evaluar($data, array_keys($this->reglas()));
    }

    /** Validación parcial para UPDATE (PUT): solo se validan los campos que vienen en el cuerpo. */
    public function validatePartial(array $data): array {
        $campos = array_values(array_filter(
            self::CAMPOS_ACTUALIZABLES,
            static fn(string $campo): bool => array_key_exists($campo, $data)
        ));

        if ($campos === []) {
            return ['body' => 'Debe enviar al menos uno de los campos: ' . implode(', ', self::CAMPOS_ACTUALIZABLES)];
        }

        return $this->evaluar($data, $campos);
    }

    private function evaluar(array $data, array $campos): array {
        $reglas = $this->reglas();
        $errores = [];

        foreach ($campos as $campo) {
            $valor = $data[$campo] ?? null;

            // El estado es opcional al crear (si no se envía, MySQL usa 'Pendiente')
            if ($campo === 'estado' && $valor === null) {
                continue;
            }

            if ($reglas[$campo]['regla']->validate($valor)->hasFailed()) {
                $errores[$campo] = $reglas[$campo]['mensaje'];
            }
        }

        return $errores;
    }
}
