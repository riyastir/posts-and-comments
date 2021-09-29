<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class APIController extends Controller
{
    /**
     * to fetch and list posts based on no of comments
     * - comments endpoint – https://jsonplaceholder.typicode.com/comments
     * -  View Single Post endpoint – https://jsonplaceholder.typicode.com/posts/{post_id}
     * -  View All Posts endpoint – https://jsonplaceholder.typicode.com/posts
     */
    public function getTopPosts()
    {

        $posts = Http::get('https://jsonplaceholder.typicode.com/posts');
        $comments = Http::get('https://jsonplaceholder.typicode.com/comments');
        $comments = json_decode($comments, true);
        $commentCounts = [];

        foreach ($comments as $comment) {
            $postId = $comment['postId'];
            if (array_key_exists($postId, $commentCounts)) {
                $commentCounts[$postId]['count'] += 1;
            } else {
                $commentCounts[$postId]['count'] = 1;
                $commentCounts[$postId]['postId'] = $postId;
            }
        }

        //$commentCounts = [['count' => 6, 'postId' => 1], ['count' => 7, 'postId' => 2], ['count' => 8, 'postId' => 3]];
        $columns_1 = array_column($commentCounts, 'count');
        array_multisort($columns_1, SORT_DESC, $commentCounts);
        
        $commentsCountCollection = collect($commentCounts);
        $commentsCountIds = $commentsCountCollection->pluck('postId');

        $sortedPosts = [];
        
        $posts = json_decode($posts,true);
        
        foreach($commentsCountIds as $key=>$dcc){
            $currentPost = [];
            $postKey = array_search($dcc, array_column($posts, 'id'));
            $commentsCount = array_search($dcc, array_column($commentCounts, 'postId'));
            $currentPost[$key]['post_id'] = $posts[$postKey]['id'];
            $currentPost[$key]['post_title'] = $posts[$postKey]['title'];
            $currentPost[$key]['post_body'] = $posts[$postKey]['body'];
            $currentPost[$key]['total_number_of_comments'] = $commentCounts[$commentsCount]['count'];
            $sortedPosts[] = $currentPost[$key];
        }
        
        return $sortedPosts;
    }

    /**
     * to Search and filter comments
     * comments endpoint – https://jsonplaceholder.typicode.com/comments
     */
    public function getSearchData(Request $request){
        $comments = Http::get('https://jsonplaceholder.typicode.com/comments');
        $comments = json_decode($comments,true);
        $keys = array_keys($request->all());
        $result = $this->filterArray($keys,$comments,$request);
        return $result;
    }

    /**
     * Recursive function to filter comments based on requested Keys
     */
    public function filterArray($keys,$array,$request){
        
        if(count($keys)==0){
            return $array;
        } else{
            $currentKey = $keys[0];
            unset($keys[0]);
            $keys = array_values($keys);
            
            $filteredComments = [];

            foreach($array as $arr){
                if($arr[$currentKey]==$request->$currentKey){
                    $filteredComments[]=$arr;
                }
            }

            return $this->filterArray($keys,$filteredComments,$request);
        }
    }
}
