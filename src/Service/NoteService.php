<?php
/**
 * Note service.
 */

namespace App\Service;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class NoteService.
 */
class NoteService implements NoteServiceInterface
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
     * Note repository.
     */
    private NoteRepository $noteRepository;

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
     * @param NoteRepository                $noteRepository       Note repository
     * @param PaginatorInterface            $paginator            Paginator
     * @param AuthorizationCheckerInterface $authorizationChecker Interface for authorization checker
     */
    public function __construct(CategoryServiceInterface $categoryService, TagServiceInterface $tagService, NoteRepository $noteRepository, PaginatorInterface $paginator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->noteRepository = $noteRepository;
        $this->paginator = $paginator;
        $this->authorizationChecker = $authorizationChecker;
        $this->tagService = $tagService;
        $this->categoryService = $categoryService;
    }

    /**
     * Get paginated list.
     *
     * @param int   $page    Page number
     * @param User  $author  Author
     * @param array $filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->noteRepository->queryAll($filters),
                $page,
                NoteRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->noteRepository->queryByAuthor($author, $filters),
            $page,
            NoteRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void
    {
        $this->noteRepository->save($note);
    }

    /**
     * Delete entity.
     *
     * @param Note $note Note entity
     */
    public function delete(Note $note): void
    {
        $this->noteRepository->delete($note);
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
