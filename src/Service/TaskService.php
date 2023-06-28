<?php
/**
 * Task service.
 */

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class TaskService.
 */
class TaskService implements TaskServiceInterface
{
    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;
    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;
    /**
     * Task repository.
     */
    private TaskRepository $taskRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Interface for authorization checker.
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface      $categoryService      Category service
     * @param TagServiceInterface           $tagService           Tag service
     * @param TaskRepository                $taskRepository       Task repository
     * @param PaginatorInterface            $paginator            Paginator
     * @param AuthorizationCheckerInterface $authorizationChecker Interface for authorization checker
     */
    public function __construct(CategoryServiceInterface $categoryService, TagServiceInterface $tagService, TaskRepository $taskRepository, PaginatorInterface $paginator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->authorizationChecker = $authorizationChecker;
        $this->tagService = $tagService;
        $this->categoryService = $categoryService;
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param User               $author  Author
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->taskRepository->queryAll($filters),
                $page,
                TaskRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->taskRepository->queryByAuthor($author, $filters),
            $page,
            TaskRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}
