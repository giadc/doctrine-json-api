<?php

namespace Giadc\DoctrineJsonApi\Filters;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Giadc\JsonApiRequest\Requests\Filters;

/**
 * Class FilterManager
 *
 * @phpstan-type CombinedFilter array{
 *      type: 'combined',
 *      keys: string[],
 *      separator?: string,
 * }
 *
 * @phpstan-type DateFilter array{
 *      type: 'date',
 *      key?: string|string[],
 * }
 *
 * @phpstan-type BasicFilter array{
 *      type: 'id'|'keyword'|'null',
 *      key?: string,
 * }
 *
 * @phpstan-type FilterTypes BasicFilter|CombinedFilter|DateFilter
 * @phpstan-type FilterInfoArray array<FilterTypes>
 */
abstract class FilterManager
{
    /**
     * @phpstan-var FilterInfoArray
     */
    protected array $accepted = [];

    /**
     * @phpstan-var array<string|int, mixed>
     */
    protected array $params = [];

    protected QueryBuilder $qb;
    protected int $paramInt = 1;
    protected string $searchDql = '';

    /**
     * @throws \Exception
     */
    public function process(QueryBuilder $qb, Filters $filters): QueryBuilder
    {
        $this->qb = $qb;

        foreach ($filters->toArray() as $key => $data) {
            if (
                count($data) > 0
                && array_key_exists($key, $this->accepted)
            ) {
                $this->processFilter($key, $data);
            }
        }

        foreach ($this->getParams() as $key => $value) {
            $this->qb->setParameter($key, $value);
        }

        return $this->qb;
    }

    /**
     * @phpstan-return array<string|int, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @phpstan-param string[] $data
     *
     * @throws \Exception
     */
    private function processFilter(string $key, array $data): void
    {
        $info   = $this->accepted[$key];
        $key    = $this->getValidKey($info, $key);
        $type   = !array_key_exists('type', $info) ? 'id' : $info['type'];
        $method = [$this, $type . 'Builder'];

        if (!is_callable($method)) {
            throw new \Exception('Invalid Type');
        }

        call_user_func($method, $data, $key);
    }

    /**
     * @phpstan-param FilterTypes $info
     * @phpstan-return string | string[]
     */
    private function getValidKey(array $info, string $key): string | array
    {
        return $key = isset($info['key']) ? $info['key'] : $key;
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-param string|string[] $key
     */
    private function idBuilder(array $data, string|array $key): void
    {
        if (is_array($key)) {
            $this->buildMultiple($data, $key);
        } else {
            $this->buildSingle($data, $key);
        }
    }

    /**
     * Build the DQL reference for the given column name. If the column is in a
     * relationship, ensure that the relationship is is included in the DQL joins.
     */
    private function getKey(string $column): string
    {
        if (strpos($column, ".") !== false) {
            $this->addInclude(explode('.', $column)[0]);
            return $column;
        }

        return 'e.' . $column;
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-param string[] $keys
     */
    private function buildMultiple(array $data, array $keys): void
    {
        $conditions = [];

        foreach ($keys as $field) {
            if (count($data) === 1) {
                $conditions[] = $this->qb->expr()->eq(
                    $this->getKey($field),
                    '?' . $this->paramInt
                );
            } else {
                $conditions[] = $this->qb->expr()->in(
                    $this->getKey($field),
                    '?' . $this->paramInt
                );
            }
        }

        $orX = $this->qb->expr()->orX();
        $orX->addMultiple($conditions);

        $this->qb->andWhere($orX);

        if (count($data) === 1) {
            $this->setParameter($this->paramInt, $data[0]);
        } else {
            $this->setParameter($this->paramInt, $data);
        }
    }

    /**
     * @phpstan-param string[] $data
     */
    private function buildSingle(array $data, string $key): void
    {
        if (is_array($data) && count($data) > 1) {
            $this->qb->andWhere($this->qb->expr()->in($this->getKey($key), '?' . $this->paramInt));
            $this->setParameter($this->paramInt, $data);

            return;
        }

        $sql = $this->getKey($key) . " = ?" . $this->paramInt;

        $this->qb->andWhere($sql);
        $this->setParameter($this->paramInt, $data[0]);
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-param string|string[] $keys
     */
    private function keywordBuilder(array $data, string|array $keys): void
    {
        if (is_array($keys)) {
            $conditions = $this->buildMultipleConditions($data, $keys);
        } else {
            $conditions = $this->buildSingleConditions($data, $keys);
        }

        $orX = $this->qb->expr()->orX();
        $orX->addMultiple($conditions);

        $this->qb->andWhere($orX);
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-param string[] $keys
     * @phpstan-return array<\Doctrine\ORM\Query\Expr\Comparison>
     */
    private function buildMultipleConditions(array $data, array $keys): array
    {
        $conditions = [];

        foreach ($keys as $key) {
            $conditions = array_merge($conditions, $this->buildSingleConditions($data, $key));
        }

        return $conditions;
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-return array<\Doctrine\ORM\Query\Expr\Comparison>
     */
    private function buildSingleConditions(array $data, string $key): array
    {
        $conditions = [];

        foreach ($data as $field) {
            array_push(
                $conditions,
                $this->qb->expr()
                    ->like($this->getKey($key), '?' . $this->paramInt)
            );

            $this->setParameter($this->paramInt, '%' . $field . '%');
        }

        return $conditions;
    }

    /**
     * @phpstan-param string[] $data
     */
    protected function nullBuilder(array $data, string $key): void
    {
        if (is_array($data)) {
            $data = $data[0];
        }

        switch ($data) {
            case 1:
                $expr = $this->qb->expr()->isNotNull('e.' . $key);
                break;
            case -1:
                $expr = null;
                break;
            case 0:
            default:
                $expr = $this->qb->expr()->isNull('e.' . $key);
                break;
        }

        if ($expr !== null) {
            $this->qb->andWhere($expr);
        }
    }

    /**
     * Build a `combined` filter
     *
     * @phpstan-param string[] $data
     */
    private function combinedBuilder(array $data, string $key): void
    {
        /** @phpstan-var CombinedFilter */
        $info      = $this->accepted[$key];
        $separator = isset($info['separator']) ? $info['separator'] : ' ';

        $conditions = $this->buildCombinedConditions($data, $info['keys'], $separator);

        $orX = $this->qb->expr()->orX();
        $orX->addMultiple($conditions);
        $this->qb->andWhere($orX);
    }

    /**
     * Build a single `combined` condition
     *
     * @phpstan-param string[] $data
     * @phpstan-param string[] $keys
     * @phpstan-return array<\Doctrine\ORM\Query\Expr\Comparison>
     */
    private function buildCombinedConditions(array $data, array $keys, string $separator): array
    {
        $conditions = [];
        $concatArrays = $this->getConcatArrays($keys, $separator);

        foreach ($data as $field) {
            foreach ($concatArrays as $entityConcatArray) {
                $leftExpr = count($entityConcatArray) > 1
                    ? new \Doctrine\ORM\Query\Expr\Func('CONCAT', $entityConcatArray)
                    : $entityConcatArray[0];

                $conditions[] = $this->qb->expr()
                    ->like($leftExpr, '?' . $this->paramInt);
            }

            $this->setParameter($this->paramInt, '%' . $field . '%');
        }

        return $conditions;
    }

    /**
     * Returns an array to be used with Doctrine's CONCAT function
     *
     * @phpstan-param string[] $keys
     * @phpstan-return array<string, array<\Doctrine\ORM\Query\Expr\Literal|string>>
     */
    private function getConcatArrays(array $keys, string $separator): array
    {
        $concatArray = [];

        foreach ($keys as $key) {
            $dqlKey = $this->getKey($key);
            $entityKey = explode('.', $dqlKey)[0];

            $concatArray[$entityKey][] = $dqlKey;
            $concatArray[$entityKey][] = $this->qb->expr()->literal($separator);
        }

        // Remove end separator for each relationship
        return array_map(function ($concatArray) {
            array_pop($concatArray);
            return $concatArray;
        }, $concatArray);
    }

    /**
     * @phpstan-param string[] $data
     * @phpstan-param string|string[] $keys
     * @throws \Exception
     */
    private function dateBuilder(array $data, string|array $keys): QueryBuilder
    {
        if (!is_array($keys)) {
            return $this->qb->andWhere($this->buildSingleDate($data, $keys));
        }

        $sql = '';

        foreach ($keys as $key) {
            if (!$sql == '') {
                $sql .= ' OR ';
            }

            $sql .= $this->buildSingleDate($data, $key);
        }

        return $this->qb->andWhere($sql);
    }

    /**
     * @phpstan-param string[] $data
     */
    private function buildSingleDate(array $data, string $key): string
    {
        if (is_array($data)) {
            $data = $data[0];
        }

        $dates = explode('-', $data);

        if (count($dates) < 2) {
            $now      = new \DateTime();
            $dates[1] = $now->format('m/d/Y');
        }

        if (!$this->validateDate($dates[0]) || !$this->validateDate($dates[1])) {
            throw new \Exception('Invalid Dates Provided');
        }

        $sql = $this->getKey($key) . " BETWEEN ?" . $this->paramInt;
        $this->setParameter($this->paramInt, \DateTime::createFromFormat('m/d/Y H:i:s', $dates[0] . '00:00:00'));

        $sql .= " AND ?" . $this->paramInt;
        $this->setParameter($this->paramInt, \DateTime::createFromFormat('m/d/Y H:i:s', $dates[1] . '23:59:59'));

        return $sql;
    }

    private function setParameter(string|int $key, mixed $value): void
    {
        $this->params[$key] = $value;
        $this->paramInt     = $this->paramInt + 1;
    }

    private function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('m/d/Y', $date);

        return $d && $d->format('m/d/Y') == $date;
    }

    private function addInclude(string $include): void
    {
        if (!$this->hasInclude($include)) {
            throw new \Exception('Invalid Include: ' . $include);
        }

        if ($this->includeExists($include)) {
            return;
        }

        $this->qb->leftJoin('e.' . $include, $include);
    }

    private function includeExists(string $include): bool
    {
        foreach ($this->qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $include) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasInclude(string $include): bool
    {
        $associations = $this->getClassMetadata()->getAssociationMappings();

        return array_key_exists($include, $associations);
    }

    protected function getClassMetadata(): ClassMetadata
    {
        $class = $this->qb->getDQLPart('from')[0]->getFrom();
        return $this->qb->getEntityManager()->getClassMetadata($class);
    }
}
