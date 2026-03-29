<?php
// src/Core/Transformer.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Core;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;
use Gbelsalvador\DataTransformer\Exceptions\ValidationException;

class Transformer
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $data = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $validationErrors = [];

    private int $rowsRead = 0;
    private int $rowsFilteredOut = 0;
    private int $rowsInvalid = 0;
    private float $startedAt = 0.0;
    private bool $loaded = false;

    public function transform(ReaderInterface $reader, WriterInterface $writer): ExecutionResult
    {
        return $this
            ->read($reader)
            ->write($writer);
    }

    public function read(ReaderInterface $reader): self
    {
        $this->startedAt = microtime(true);
        $this->data = $reader->read();
        $this->rowsRead = count($this->data);
        $this->rowsFilteredOut = 0;
        $this->rowsInvalid = 0;
        $this->validationErrors = [];
        $this->loaded = true;

        return $this;
    }

    public function filter(callable $callback): self
    {
        $this->ensureDataIsLoaded();
        $filtered = [];

        foreach ($this->data as $index => $row) {
            if ($callback($row, $index) === true) {
                $filtered[] = $row;
                continue;
            }

            $this->rowsFilteredOut++;
        }

        $this->data = $filtered;

        return $this;
    }

    public function map(callable|array $mapping): self
    {
        $this->ensureDataIsLoaded();
        $mapped = [];

        foreach ($this->data as $index => $row) {
            if (is_callable($mapping)) {
                $newRow = $mapping($row, $index);
                if (!is_array($newRow)) {
                    throw new TransformerException('The map callable must return an array row.');
                }

                $mapped[] = $newRow;
                continue;
            }

            $mapped[] = $this->applyMappingRules($row, $mapping, $index);
        }

        $this->data = $mapped;

        return $this;
    }

    /**
     * @param array<string, string|array<int, string>> $rules
     */
    public function validate(array $rules): self
    {
        $this->ensureDataIsLoaded();
        $validRows = [];

        foreach ($this->data as $index => $row) {
            $rowErrors = $this->validateRow($row, $rules, $index);

            if ($rowErrors === []) {
                $validRows[] = $row;
                continue;
            }

            $this->rowsInvalid++;
            $this->validationErrors[] = [
                'row' => $index,
                'errors' => $rowErrors,
            ];
        }

        $this->data = $validRows;

        return $this;
    }

    public function write(WriterInterface $writer): ExecutionResult
    {
        $this->ensureDataIsLoaded();
        $writer->write($this->data);

        return new ExecutionResult(
            rowsRead: $this->rowsRead,
            rowsWritten: count($this->data),
            rowsFilteredOut: $this->rowsFilteredOut,
            rowsInvalid: $this->rowsInvalid,
            durationSeconds: microtime(true) - $this->startedAt,
            validationErrors: $this->validationErrors
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function data(): array
    {
        $this->ensureDataIsLoaded();
        return $this->data;
    }

    /**
     * @param array<string, string|callable> $mapping
     * @return array<string, mixed>
     */
    private function applyMappingRules(array $row, array $mapping, int $index): array
    {
        $mappedRow = [];

        foreach ($mapping as $targetField => $rule) {
            if (is_callable($rule)) {
                $mappedRow[$targetField] = $rule($row, $index);
                continue;
            }

            $mappedRow[$targetField] = $row[$rule] ?? null;
        }

        return $mappedRow;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, string|array<int, string>> $rules
     * @return array<string, array<int, string>>
     */
    private function validateRow(array $row, array $rules, int $index): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $row[$field] ?? null;
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            foreach ($fieldRules as $rule) {
                $message = $this->applyValidationRule($field, $value, $rule, $row);
                if ($message !== null) {
                    $errors[$field][] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function applyValidationRule(string $field, mixed $value, string $rule, array $row): ?string
    {
        [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

        return match ($name) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'numeric' => $this->validateNumeric($field, $value),
            'integer' => $this->validateInteger($field, $value),
            'boolean' => $this->validateBoolean($field, $value),
            'date' => $this->validateDate($field, $value),
            'max' => $this->validateMax($field, $value, $parameter),
            'in' => $this->validateIn($field, $value, $parameter),
            'same' => $this->validateSame($field, $value, $parameter, $row),
            default => throw new ValidationException("Unsupported validation rule '{$name}' for field '{$field}'."),
        };
    }

    private function validateRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return "The field '{$field}' is required.";
        }

        return null;
    }

    private function validateEmail(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return "The field '{$field}' must contain a valid email address.";
        }

        return null;
    }

    private function validateNumeric(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return "The field '{$field}' must be numeric.";
        }

        return null;
    }

    private function validateInteger(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "The field '{$field}' must be an integer.";
        }

        return null;
    }

    private function validateBoolean(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            return "The field '{$field}' must be a boolean.";
        }

        return null;
    }

    private function validateDate(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (strtotime((string) $value) === false) {
            return "The field '{$field}' must be a valid date.";
        }

        return null;
    }

    private function validateMax(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null || $value === '' || $parameter === null) {
            return null;
        }

        if (strlen((string) $value) > (int) $parameter) {
            return "The field '{$field}' must not exceed {$parameter} characters.";
        }

        return null;
    }

    private function validateIn(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null || $value === '' || $parameter === null) {
            return null;
        }

        $allowedValues = array_map('trim', explode(',', $parameter));
        if (!in_array((string) $value, $allowedValues, true)) {
            return "The field '{$field}' must be one of: {$parameter}.";
        }

        return null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function validateSame(string $field, mixed $value, ?string $parameter, array $row): ?string
    {
        if ($parameter === null) {
            return null;
        }

        if (($row[$parameter] ?? null) !== $value) {
            return "The field '{$field}' must match '{$parameter}'.";
        }

        return null;
    }

    private function ensureDataIsLoaded(): void
    {
        if ($this->loaded === false) {
            throw new TransformerException('No data loaded. Call read() or transform() first.');
        }
    }
}
