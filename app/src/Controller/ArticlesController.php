<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Database\Expression\QueryExpression;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @method \App\Model\Entity\Article[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ArticlesController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Load the Articles table
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');

        // Build query to fetch articles with like count
        $query = $articlesTable->find();
        $query->select([
                'Articles.id',
                'Articles.title',
                'Articles.created_at',
                'like_count' => $query->func()->count('ArticleLikes.id')
            ])
            ->leftJoinWith('ArticleLikes')
            ->group(['Articles.id', 'Articles.title', 'Articles.created_at']);

        // Configure pagination settings
        $this->paginate = [
            'limit' => 10,
            'order' => ['Articles.created' => 'desc'],
            'contain' => ['ArticleLikes'],
        ];

        try {
            $articles = $this->paginate($query);

            // Pass articles data to the view
            $this->set(compact('articles'));
            $this->viewBuilder()->setOption('serialize', ['articles']);

        } catch (RecordNotFoundException $e) {
            $this->set([
                'error' => 'No articles found.',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
        }
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        try {
            $article = $this->Articles->get($id, [
                'contain' => ['Users'],
            ]);

            // Load the ArticleLikes table
            $articleLikesTable = TableRegistry::getTableLocator()->get('ArticleLikes');

            // Fetch LikeCount for the article
            $likeCountQuery = $articleLikesTable->find();
            $likeCountQuery->select(['like_count' => $likeCountQuery->func()->count('*')])
                ->where(['article_id' => $article->id]);

            $likeCountResult = $likeCountQuery->first();

            $article->like_count = $likeCountResult->like_count ?? 0;

            $this->set(compact('article'));
            $this->viewBuilder()->setOption('serialize', ['article']);

        } catch (RecordNotFoundException $e) {
            $this->set([
                'error' => 'No article found.',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $article->user_id = $this->Authentication->getIdentity()->id;

            // Set created_at and updated_at timestamps
            $article->created_at = new \DateTime();
            $article->updated_at = new \DateTime();

            if (!$this->Articles->save($article)) {
                $this->set([
                    'error' => 'The article could not be saved. Please, try again.',
                ]);
                $this->viewBuilder()->setOption('serialize', ['error']);
            }
        }
        $users = $this->Articles->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('article', 'users'));
        $this->viewBuilder()->setOption('serialize', ['article', 'user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        // Get the currently authenticated user's ID
        $userId = $this->Authentication->getIdentity()->id;

        $article = $this->Articles->find()
                    ->where(['user_id' => $userId, 'id' => $id])
                    ->first();
        if(!$article) {
            $this->set([
                'error' => 'Could not find article. Please, try again.',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
        } else {
            if ($this->request->is(['put'])) {
                $article = $this->Articles->patchEntity($article, $this->request->getData());
    
                // Set created_at and updated_at timestamps
                $article->created_at = new \DateTime();
                $article->updated_at = new \DateTime();
    
                if (!$this->Articles->save($article)) {
                    $this->set([
                        'error' => 'The article could not be saved. Please, try again.',
                    ]);
                    $this->viewBuilder()->setOption('serialize', ['error']);
                }
            }
            $users = $this->Articles->Users->find('list', ['limit' => 200])->all();
            $this->set(compact('article', 'users'));
            $this->viewBuilder()->setOption('serialize', ['article', 'users']);
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['delete']);
        // Get the currently authenticated user's ID
        $userId = $this->Authentication->getIdentity()->id;

        $article = $this->Articles->find()
                    ->where(['user_id' => $userId, 'id' => $id])
                    ->first();
        
        if(!$article) {
            $this->set([
                'error' => 'Could not find article. Please, try again.',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
        } else {

            if ($this->Articles->delete($article)) {
                $this->set([
                    'message' => __('The article has been deleted.'),
                ]);
            } else {
                $this->set([
                    'message' => __('The article could not be deleted. Please, try again.'),
                ]);
            }
        }

        $this->viewBuilder()->setOption('serialize', ['message']);
    }

    /*
    Like Article
    */
    public function like($articleId)
    {
        $this->request->allowMethod(['post']);
        $userId = $this->Authentication->getIdentity()->id;

        $article = $this->Articles->find()
                    ->where(['id' => $articleId])
                    ->first();
        
        if(!$article) {
            $this->set([
                'error' => 'Could not find article. Please, try again.',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
        } else {
            // Check if the user has already liked the article
            $liked = $this->Articles->ArticleLikes->exists(['user_id' => $userId, 'article_id' => $articleId]);
                    
            if (!$liked) {
                $articleLike = $this->Articles->ArticleLikes->newEmptyEntity();
                $articleLike->user_id = $userId;
                $articleLike->article_id = $articleId;
                $articleLike->created_at = new \DateTime();

                if ($this->Articles->ArticleLikes->save($articleLike)) {
                    $this->set([
                        'message' => __('Article liked.'),
                    ]);
                } else {
                    $this->set([
                        'message' => __('Failed to like the article.'),
                    ]);
                }
            } else {
                $this->set([
                    'message' => __('You have already liked this article.'),
                ]);
            }
            $this->viewBuilder()->setOption('serialize', ['message']);
        }
    }
}
