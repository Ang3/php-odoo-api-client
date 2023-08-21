<?php

namespace Ang3\Component\Odoo;

use Ang3\Component\Odoo\DBAL\Expression\DomainInterface;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Expression\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\Interfaces\ModelInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Model implements UrlRoutable, Arrayable, JsonSerializable, ModelInterface
{
    use ValidatesRequests,
        HasRelationships;

    protected $model;

    protected $routeKey;

    protected ?int $id = null;

    protected $fillable = [];

    private DomainInterface $condition;

    private array $select = [];

    private array $options = [];

    private Client $client;

    private RecordManager $rm;

    public function __construct()
    {
        $this->client = new Client(getenv('ODOO_URL'), getenv('ODOO_DATABASE'), getenv('ODOO_USER'), getenv('ODOO_PWD'));
        $this->rm = new RecordManager($this->client);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!isset($this->{$key}))
            if (method_exists($this, $key)) {
                if ($this->relationLoaded($key))
                    return $this->getRelation($key);
                else {
                    $value = $this->{$key}();
                    $this->setRelation($key, $value);
                }
                return $value;
            } else
                return null;
        return $this->{$key};
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $key, mixed $value)
    {
        $this->addAttribute($key, $value);
    }

    private function addAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->addAttribute($key, $value);
        }
        return $this;
    }

    private function addAttribute(string $key, mixed $value): self
    {
        if (!in_array($key, $this->fillable))
            $this->fillable[] = $key;
        $this->{$key} = $value;
        return $this;
    }

    public function getAttributes(): array
    {
        $attributes = [];

        foreach ($this->fillable as $field) {
            $value = $this->{$field};
            if (!($value instanceof Model))
                $attributes[$field] = is_array($value) ? $value[0] : $value;
        }

        return $attributes;
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->fillable as $field) {
            $value = $this->{$field};
            $array[$field] = $value instanceof Arrayable ? $value->toArray() : $value;
        }
        return $array;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getRouteKey()
    {
        return $this->id;
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return self::resolveRouteBindingQuery($this, $value);
    }

    public function resolveRouteBindingQuery($model, $id): static
    {
        if (!is_numeric($id))
            throw new NotFoundHttpException($model->model . ' not found');
        return $model->findOrFail($id);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return null;
    }

    public function getResult(): array
    {
        if (empty($this->select))
            $this->select = $this->fillable;

        $this->fillable = [];

        $data = $this->rm
            ->createQueryBuilder($this->model)
            ->where($this->condition)
            ->getQuery()
            ->setOptions($this->options)
            ->addOption('fields', $this->select)
            ->addOption('context', ['lang' => request()->lang])
            ->getResult();

        $this->clearUseVars();

        return $data;
    }

    private function getOneOrNullResult(): array | null
    {
        if (empty($this->select))
            $this->select = $this->fillable;

        $this->fillable = [];

        $data = $this->rm
            ->createQueryBuilder($this->model)
            ->where($this->condition)
            ->getQuery()
            ->setOptions($this->options)
            ->addOption('fields', $this->select)
            ->addOption('context', ['lang' => request()->lang])
            ->getOneOrNullResult();

        $this->clearUseVars();

        return $data;
    }

    public function count(): int
    {
        $count = $this->rm->count($this->model, $this->condition);

        $this->clearUseVars();

        return $count;
    }

    private function clearUseVars(): void
    {
        unset($this->condition);
        $this->select = [];
        $this->options = [];
    }

    public function get(): Collection
    {
        $collection = [];

        $data = $this->getResult();
        foreach ($data as $d) {
            $model = new static();
            $model->fillable = [];
            $collection[] = $model->addAttributes($d);
        }
        return new Collection($collection);
    }

    public static function where(DomainInterface $condition): self
    {
        $model = new static();
        $model->condition = $condition;
        return $model;
    }

    public function select(array $select): self
    {
        $this->select = $select;
        return $this;
    }

    private function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->setOption($option[0], $option[1]);
        }
        return $this;
    }

    public function find(int $id): mixed
    {
        $findExpr = (new ExpressionBuilder())->eq('id', $id);

        if (empty($condition))
            $this->condition = $findExpr;
        else
            $this->condition = new CompositeDomain(CompositeDomain::AND, [$this->condition, $findExpr]);

        $data = $this->getOneOrNullResult();
        return $this->addAttributes($data ?? ['id' => $id]);
    }

    public function findOrFail(int $id): self
    {
        $findExpr = (new ExpressionBuilder())->eq('id', $id);

        if (empty($this->condition))
            $this->condition = $findExpr;
        else
            $this->condition = new CompositeDomain(CompositeDomain::AND, [$this->condition, $findExpr]);

        $data = $this->getOneOrNullResult();

        if (is_null($data))
            throw new NotFoundHttpException($this->model . ' not found');
        return $this->addAttributes($data);
    }

    public function findBy(string|int $field, $value): self
    {
        $findExpr = (new ExpressionBuilder())->eq($field, $value);

        if (empty($this->condition))
            $this->condition = $findExpr;
        else
            $this->condition = new CompositeDomain(CompositeDomain::AND, [$this->condition, $findExpr]);

        $data = $this->getOneOrNullResult();
        return $this->addAttributes($data ?? []);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $relations = collect();
        if (is_null($relations)) {
            $model = new $related();
            $foreignKey = $foreignKey ?? str_replace('.', '_', $model->model) . '_id';
            $localKey = is_array($this->{$localKey}) ? $this->{$localKey}[0] : $this->{$localKey} ?? $this->id;
            $relations = $related::where((new ExpressionBuilder())->eq($foreignKey, $localKey))->get();
        }
        return $relations;
    }

    public function belongsTo($related, $foreignKey = null, $localKey = null)
    {
        $model = new $related();
        $foreignKey = $foreignKey ?? str_replace('.', '_', $model->model) . '_id';
        $localKey = is_array($this->{$localKey}) ? $this->{$localKey}[0] : $this->{$localKey} ?? $this->id;
        $relation = $model->findBy($foreignKey, $localKey);

        return $relation;
    }

    public function save(): self
    {
        $model = $this;
        if (is_null($this->id)) {
            $this->id = $this->client->create($this->model, array_filter($this->getAttributes(), fn ($v) => !is_null($v)));
            $model = $model->findOrFail($this->id);
        } else
            $this->rm->update($this->model, $this->id, $this->getAttributes());

        return $model;
    }

    public static function create(array $attributes): self
    {
        $model = new static();
        $model->addAttributes($attributes);
        return $model->save();
    }

    public function delete(): bool
    {
        $this->rm->delete($this->model, $this->id);
        return true;
    }

    public function paginate(int $per_page = 100): array
    {
        $this->validate(request(), [
            'page' => 'integer|min:1',
            'from_page' => 'integer|min:1',
        ]);

        $page = (int) request()->input('page', '1');
        $from_page = (int) request()->input('from_page');

        $demand_per_page = $per_page;
        if ($from_page > $page)
            throw new \Exception('from_page must be less than page');

        if (isset($from_page) && $from_page !== 0) {
            $per_page = ($page - $from_page) * $demand_per_page;
            $offset = ($from_page - 1) * $demand_per_page;
        } else
            $offset = ($page - 1) * $demand_per_page;

        $count = new static();
        $count->condition = $this->condition;
        $count = $count->count();

        $this->setOptions([
            ['limit', $per_page],
            ['offset', $offset],
            ['order', 'id DESC']
        ]);

        $data = $this->get();

        $last_page = ceil($count / $demand_per_page);

        return [
            'current_page' => $page,
            'previous_page' => $page > 1 ? $page - 1 : 1,
            'next_page' => $page < $last_page ? $page + 1 : $last_page,
            'last_page' => $last_page,
            'per_page' => $demand_per_page,
            'total' => $count,
            'data' => $data
        ];;
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }
}
