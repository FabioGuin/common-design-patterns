<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $comments = $this->commentService->getComments($request->all());
            
            Log::info('Comments list retrieved', [
                'count' => $comments->count(),
                'filters' => $request->all()
            ]);

            return view('resource-controllers.comments.index', compact('comments'));
        } catch (\Exception $e) {
            Log::error('Error retrieving comments list', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Errore nel recupero dei commenti.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $postId = $request->get('post_id');
        $post = $postId ? Post::findOrFail($postId) : null;
        $posts = Post::all();
        
        return view('resource-controllers.comments.create', compact('post', 'posts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentRequest $request)
    {
        try {
            $commentData = $request->validated();
            $comment = $this->commentService->createComment($commentData);

            Log::info('Comment created successfully', [
                'comment_id' => $comment->id,
                'post_id' => $comment->post_id
            ]);

            return redirect()->route('posts.show', $comment->post)
                ->with('success', 'Commento creato con successo!');
        } catch (\Exception $e) {
            Log::error('Error creating comment', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nella creazione del commento: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        try {
            $comment->load(['post', 'user']);
            
            Log::info('Comment retrieved', [
                'comment_id' => $comment->id,
                'post_id' => $comment->post_id
            ]);

            return view('resource-controllers.comments.show', compact('comment'));
        } catch (\Exception $e) {
            Log::error('Error retrieving comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('comments.index')
                ->with('error', 'Errore nel recupero del commento.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        $posts = Post::all();
        return view('resource-controllers.comments.edit', compact('comment', 'posts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommentRequest $request, Comment $comment)
    {
        try {
            $commentData = $request->validated();
            $this->commentService->updateComment($comment, $commentData);

            Log::info('Comment updated successfully', [
                'comment_id' => $comment->id,
                'post_id' => $comment->post_id
            ]);

            return redirect()->route('posts.show', $comment->post)
                ->with('success', 'Commento aggiornato con successo!');
        } catch (\Exception $e) {
            Log::error('Error updating comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nell\'aggiornamento del commento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        try {
            $postId = $comment->post_id;
            $this->commentService->deleteComment($comment);

            Log::info('Comment deleted successfully', [
                'comment_id' => $comment->id,
                'post_id' => $postId
            ]);

            return redirect()->route('posts.show', $postId)
                ->with('success', 'Commento eliminato con successo!');
        } catch (\Exception $e) {
            Log::error('Error deleting comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'eliminazione del commento: ' . $e->getMessage());
        }
    }

    /**
     * Approve comment
     */
    public function approve(Comment $comment)
    {
        try {
            $this->commentService->approveComment($comment);

            Log::info('Comment approved', [
                'comment_id' => $comment->id
            ]);

            return redirect()->back()
                ->with('success', 'Commento approvato con successo!');
        } catch (\Exception $e) {
            Log::error('Error approving comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'approvazione del commento: ' . $e->getMessage());
        }
    }

    /**
     * Reject comment
     */
    public function reject(Comment $comment)
    {
        try {
            $this->commentService->rejectComment($comment);

            Log::info('Comment rejected', [
                'comment_id' => $comment->id
            ]);

            return redirect()->back()
                ->with('success', 'Commento rifiutato con successo!');
        } catch (\Exception $e) {
            Log::error('Error rejecting comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nel rifiuto del commento: ' . $e->getMessage());
        }
    }

    /**
     * Get comments for a specific post
     */
    public function forPost(Post $post)
    {
        try {
            $comments = $this->commentService->getCommentsForPost($post);
            
            Log::info('Comments for post retrieved', [
                'post_id' => $post->id,
                'count' => $comments->count()
            ]);

            return view('resource-controllers.comments.index', compact('comments', 'post'));
        } catch (\Exception $e) {
            Log::error('Error retrieving comments for post', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('posts.show', $post)
                ->with('error', 'Errore nel recupero dei commenti per il post.');
        }
    }

    /**
     * API endpoint for comments
     */
    public function apiIndex(Request $request)
    {
        try {
            $comments = $this->commentService->getComments($request->all());
            
            return CommentResource::collection($comments);
        } catch (\Exception $e) {
            Log::error('Error in API comments index', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero dei commenti'
            ], 500);
        }
    }

    /**
     * API endpoint for single comment
     */
    public function apiShow(Comment $comment)
    {
        try {
            $comment->load(['post', 'user']);
            
            return new CommentResource($comment);
        } catch (\Exception $e) {
            Log::error('Error in API comment show', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero del commento'
            ], 500);
        }
    }
}
