# Extbase Patterns Reference for TYPO3 v14

Based on the official TYPO3 Core API and the tea best-practices extension.

## Controller

All controllers extend `ActionController`. Every action MUST return `ResponseInterface`.

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Controller;

use Psr\Http\Message\ResponseInterface;
use Vendor\MyExtension\Domain\Model\Item;
use Vendor\MyExtension\Domain\Repository\ItemRepository;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\ErrorController;

final class ItemController extends ActionController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly ErrorController $errorController,
    ) {}

    public function listAction(): ResponseInterface
    {
        $this->view->assign('items', $this->itemRepository->findAll());
        return $this->htmlResponse();
    }

    public function showAction(?Item $item = null): ResponseInterface
    {
        if ($item === null) {
            $this->trigger404('No item given.');
        }
        $this->view->assign('item', $item);
        return $this->htmlResponse();
    }

    /**
     * @return never
     * @throws PropagateResponseException
     */
    protected function trigger404(string $message): void
    {
        throw new PropagateResponseException(
            $this->errorController->pageNotFoundAction($this->request, $message),
            1700000000,
        );
    }

    // Forward to another action
    protected function forwardExample(): ResponseInterface
    {
        return $this->redirect('list');
    }

    // Flash message
    protected function createAction(Item $item): ResponseInterface
    {
        $this->itemRepository->add($item);
        $this->addFlashMessage('Item created successfully.');
        return $this->redirect('list');
    }

    // JSON response
    public function apiAction(): ResponseInterface
    {
        $data = ['items' => []];
        return $this->jsonResponse(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
```

**Key rules:**
- Mark controllers `final`
- Use `readonly` constructor injection
- Return `$this->htmlResponse()` or `$this->jsonResponse()` — never `void`
- Use `PropagateResponseException` for 404s (tea pattern)
- Access settings via `$this->settings['key']`
- FlexForm settings with `settings.` prefix are auto-merged

---

## Domain Model

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Item extends AbstractEntity
{
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    #[Extbase\Validate(['validator' => 'StringLength', 'options' => ['maximum' => 255]])]
    protected string $title = '';

    #[Extbase\Validate(['validator' => 'StringLength', 'options' => ['maximum' => 2000]])]
    protected string $description = '';

    protected int $amount = 0;

    protected bool $featured = false;

    protected ?\DateTimeInterface $publishDate = null;

    #[Extbase\ORM\Lazy]
    protected FileReference|LazyLoadingProxy|null $image = null;

    /**
     * @var ObjectStorage<Tag>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $tags;

    /**
     * @var ObjectStorage<ChildItem>
     */
    #[Extbase\ORM\Lazy]
    #[Extbase\ORM\Cascade(['value' => 'remove'])]
    protected ObjectStorage $children;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        $this->tags = new ObjectStorage();
        $this->children = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImage(): ?FileReference
    {
        if ($this->image instanceof LazyLoadingProxy) {
            $image = $this->image->_loadRealInstance();
            $this->image = ($image instanceof FileReference) ? $image : null;
        }
        return $this->image;
    }

    public function setImage(FileReference $image): void
    {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        $this->tags->attach($tag);
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->detach($tag);
    }
}
```

**Key rules:**
- Use Extbase annotation attributes for validation and ORM
- Initialize ObjectStorage in `initializeObject()` AND `__construct()`
- Handle LazyLoadingProxy explicitly for FileReference (tea pattern)
- Use typed properties with defaults
- `@var ObjectStorage<Type>` for generics

---

## Repository

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Domain\Repository;

use Vendor\MyExtension\Domain\Model\Item;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Item>
 */
class ItemRepository extends Repository
{
    protected $defaultOrderings = ['title' => QueryInterface::ORDER_ASCENDING];

    /**
     * @return QueryResultInterface<Item>
     */
    public function findBySearchQuery(string $search): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalOr(
                $query->like('title', '%' . $search . '%'),
                $query->like('description', '%' . $search . '%'),
            )
        );
        return $query->execute();
    }

    /**
     * @return QueryResultInterface<Item>
     */
    public function findFeatured(int $limit = 10): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('featured', true));
        $query->setLimit($limit);
        $query->setOrderings(['publishDate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * @param positive-int $ownerUid
     * @return QueryResultInterface<Item>
     */
    public function findByOwnerUid(int $ownerUid): QueryResultInterface
    {
        $query = $this->createQuery();
        // Ignore storage page restriction
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false)
        );
        $query->matching($query->equals('ownerUid', $ownerUid));
        return $query->execute();
    }
}
```

Query constraint methods: `equals()`, `like()`, `contains()`, `in()`, `lessThan()`, `greaterThan()`, `lessThanOrEqual()`, `greaterThanOrEqual()`, `logicalAnd()`, `logicalOr()`, `logicalNot()`.

---

## Fluid Templates

### Layout

```html
<!-- Resources/Private/Layouts/Default.html -->
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">
<f:render section="Content" />
</html>
```

### List Template

```html
<!-- Resources/Private/Templates/Item/List.html -->
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">
<f:layout name="Default" />

<f:section name="Content">
    <h1><f:translate key="list.header" /></h1>

    <f:if condition="{items}">
        <f:then>
            <table class="table">
                <thead>
                    <tr>
                        <th><f:translate key="list.title" /></th>
                        <th><f:translate key="list.description" /></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <f:for each="{items}" as="item">
                        <tr>
                            <td>{item.title}</td>
                            <td><f:format.crop maxCharacters="100">{item.description}</f:format.crop></td>
                            <td>
                                <f:link.action action="show" arguments="{item: item}">
                                    <f:translate key="list.show" />
                                </f:link.action>
                            </td>
                        </tr>
                    </f:for>
                </tbody>
            </table>
        </f:then>
        <f:else>
            <p><f:translate key="list.empty" /></p>
        </f:else>
    </f:if>
</f:section>
</html>
```

### Show Template

```html
<!-- Resources/Private/Templates/Item/Show.html -->
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">
<f:layout name="Default" />

<f:section name="Content">
    <h1>{item.title}</h1>

    <f:if condition="{item.image}">
        <f:image image="{item.image}" maxWidth="800" class="img-fluid" loading="lazy" />
    </f:if>

    <div><f:format.html>{item.description}</f:format.html></div>

    <f:if condition="{item.publishDate}">
        <p class="text-muted">
            <f:translate key="show.published" />:
            <f:format.date format="d.m.Y">{item.publishDate}</f:format.date>
        </p>
    </f:if>

    <f:link.action action="list"><f:translate key="show.back" /></f:link.action>
</f:section>
</html>
```

---

## Pagination

```php
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;

public function listAction(int $currentPage = 1): ResponseInterface
{
    $items = $this->itemRepository->findAll();
    $paginator = new QueryResultPaginator($items, $currentPage, (int)($this->settings['itemsPerPage'] ?? 10));
    $pagination = new SlidingWindowPagination($paginator, 5);

    $this->view->assignMultiple([
        'items' => $paginator->getPaginatedItems(),
        'pagination' => $pagination,
        'paginator' => $paginator,
    ]);
    return $this->htmlResponse();
}
```

Pagination partial:
```html
<!-- Resources/Private/Partials/Pagination.html -->
<nav aria-label="Pagination">
    <ul class="pagination">
        <f:if condition="{pagination.previousPageNumber}">
            <li class="page-item">
                <f:link.action arguments="{currentPage: pagination.previousPageNumber}" class="page-link">
                    Previous
                </f:link.action>
            </li>
        </f:if>
        <f:for each="{pagination.allPageNumbers}" as="page">
            <li class="page-item {f:if(condition: '{page} == {paginator.currentPageNumber}', then: 'active')}">
                <f:link.action arguments="{currentPage: page}" class="page-link">{page}</f:link.action>
            </li>
        </f:for>
        <f:if condition="{pagination.nextPageNumber}">
            <li class="page-item">
                <f:link.action arguments="{currentPage: pagination.nextPageNumber}" class="page-link">
                    Next
                </f:link.action>
            </li>
        </f:if>
    </ul>
</nav>
```

Other paginators: `ArrayPaginator` for arrays, `SimplePagination` for basic prev/next.

---

## Validation

### Built-in validators (via attributes)

```php
#[Extbase\Validate(['validator' => 'NotEmpty'])]
#[Extbase\Validate(['validator' => 'StringLength', 'options' => ['minimum' => 3, 'maximum' => 255]])]
#[Extbase\Validate(['validator' => 'Integer'])]
#[Extbase\Validate(['validator' => 'Float'])]
#[Extbase\Validate(['validator' => 'NumberRange', 'options' => ['minimum' => 1, 'maximum' => 100]])]
#[Extbase\Validate(['validator' => 'EmailAddress'])]
#[Extbase\Validate(['validator' => 'Url'])]
#[Extbase\Validate(['validator' => 'RegularExpression', 'options' => ['regularExpression' => '/^[A-Z]/'])])]
#[Extbase\Validate(['validator' => 'DateTime'])]
#[Extbase\Validate(['validator' => 'Boolean'])]
```

### Custom validator

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class UniqueSlugValidator extends AbstractValidator
{
    protected function isValid(mixed $value): void
    {
        if (!$this->isSlugUnique($value)) {
            $this->addError(
                $this->translateErrorMessage('validator.slug.notUnique', 'my_extension'),
                1700000001
            );
        }
    }
}
```

Use in model: `#[Extbase\Validate(['validator' => 'Vendor\\MyExtension\\Validation\\Validator\\UniqueSlugValidator'])]`

---

## Routing (Route Enhancers)

```yaml
# config/sites/main/config.yaml
routeEnhancers:
  MyExtensionList:
    type: Extbase
    extension: MyExtension
    plugin: List
    routes:
      - routePath: '/items/{page}'
        _controller: 'Item::list'
        _arguments:
          page: '@widget_0/currentPage'
      - routePath: '/item/{item}'
        _controller: 'Item::show'
        _arguments:
          item: item
    defaultController: 'Item::list'
    aspects:
      page:
        type: StaticRangeMapper
        start: '1'
        end: '100'
      item:
        type: PersistedAliasMapper
        tableName: tx_myext_domain_model_item
        routeFieldName: slug
```

Aspect types: `StaticRangeMapper`, `StaticValueMapper`, `PersistedAliasMapper`, `PersistedPatternMapper`, `LocaleModifier`.

---

## Form Handling (Create/Edit)

### Controller

```php
public function newAction(): ResponseInterface
{
    $this->view->assign('item', new Item());
    return $this->htmlResponse();
}

public function createAction(Item $item): ResponseInterface
{
    $this->itemRepository->add($item);
    $this->addFlashMessage('Created.');
    return $this->redirect('list');
}

public function editAction(Item $item): ResponseInterface
{
    $this->view->assign('item', $item);
    return $this->htmlResponse();
}

public function updateAction(Item $item): ResponseInterface
{
    $this->itemRepository->update($item);
    $this->addFlashMessage('Updated.');
    return $this->redirect('list');
}

public function deleteAction(Item $item): ResponseInterface
{
    $this->itemRepository->remove($item);
    $this->addFlashMessage('Deleted.');
    return $this->redirect('list');
}
```

### Form Template

```html
<f:form action="create" object="{item}" name="item">
    <f:form.validationResults for="item">
        <f:if condition="{validationResults.hasErrors}">
            <div class="alert alert-danger">
                <f:for each="{validationResults.flattenedErrors}" key="property" as="errors">
                    <f:for each="{errors}" as="error">
                        <p>{property}: {error.message}</p>
                    </f:for>
                </f:for>
            </div>
        </f:if>
    </f:form.validationResults>

    <div class="mb-3">
        <label for="title"><f:translate key="form.title" /></label>
        <f:form.textfield property="title" id="title" class="form-control" />
    </div>
    <div class="mb-3">
        <label for="description"><f:translate key="form.description" /></label>
        <f:form.textarea property="description" id="description" rows="5" class="form-control" />
    </div>
    <f:form.submit value="{f:translate(key: 'form.submit')}" class="btn btn-primary" />
</f:form>
```
