<?php
namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\Sorting;

trait Processors
{
    /**
     * Add SELECT and LEFT JOIN statements for all includes
     *
     * @param  QueryBuilder $qb
     * @param  Includes     $includes
     * @return QueryBuilder
     */
    protected function processIncludes(QueryBuilder $qb, Includes $includes)
    {
        foreach ($includes->toArray() as $include) {
            if (!$this->hasAssociation($include) || $this->includeExists($qb, $include)) {
                continue;
            }

            (strpos($include, '.') === false)
                ? $this->processSingleInclude($qb, $include)
                : $this->processNestedInclude($qb, $include);
        }

        return $qb;
    }

    /**
     * Process a single include
     *
     * @param  QueryBuilder $qb
     * @param  string       $include
     * @return void
     */
    protected function processSingleInclude(QueryBuilder $qb, string $include)
    {
        $qb->leftJoin('e.' . $include, $include);
        $qb->addSelect($include);
    }

    /**
     * Process nested includes
     *
     * @param  QueryBuilder $qb
     * @param  string       $include
     * @return void
     */
    protected function processNestedInclude(QueryBuilder $qb, string $include)
    {
        $includePieces = explode('.', $include);
        array_unshift($includePieces, 'e');

        for ($i = 1; $i < count($includePieces); $i++) {
            if ($this->includeExists($qb, $includePieces[$i])) {
                continue;
            }

            $qb->leftJoin($includePieces[$i - 1] . '.' . $includePieces[$i], $includePieces[$i]);
            $qb->addSelect($includePieces[$i]);
        }
    }

    /**
     * Determines if an include has already been processed
     *
     * @param  QueryBuilder $qb
     * @param  string       $include
     * @return bool
     */
    protected function includeExists(QueryBuilder $qb, $include)
    {
        foreach ($qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $include) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process Sorting
     *
     * @param  QueryBuilder $qb
     * @param  Sorting      $sort
     * @return QueryBuilder
     */
    protected function processSorting(QueryBuilder $qb, Sorting $sort)
    {
        $sortArray = $sort->toArray();

        if (empty(array_filter($sortArray))) {
            $sortArray = $this->getDefaultSort();
        }

        foreach ($sortArray as $key => $field) {
            if ($this->hasField($field['field'])) {
                $qb->addOrderBy('e.' . $field['field'], $field['direction']);
            }
        }

        return $qb;
    }

    /**
     * Add offset and page size
     *
     * @param  QueryBuilder $query
     * @param  Pagination   $page
     * @return Paginator
     */
    public function paginate(QueryBuilder $query, Pagination $page)
    {
        $query = $query->setParameters($query->getParameters())
            ->setFirstResult($page->getOffset() * $page->getPageSize())
            ->setMaxResults($page->getPageSize());

        return new Paginator($query, true);
    }

    /**
     * Determine if the current Class has a field
     *
     * @param  string  $field
     * @return boolean
     */
    protected function hasField($field)
    {
        $fields = $this->getClassMetadata($this->class);

        return $fields->hasField($field);
    }

    /**
     * Determine if the current Class has an association
     *
     * @param  string  $association
     * @return boolean
     */
    protected function hasAssociation($association)
    {
        $associations = $this->getClassMetadata()->getAssociationMappings();

        if (strpos($association, '.')) {
            $associationPieces = explode('.', $association);
            return array_key_exists($associationPieces[0], $associations);
        }

        return array_key_exists($association, $associations);
    }

    /**
     * Return the metadata mapping for the current Class
     *
     * @return array
     */
    protected function getClassMetadata()
    {
        return $this->em->getClassMetadata($this->class);
    }
}
