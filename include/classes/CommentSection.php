<?php

class CommentSection
{
    private $conn, $video, $userLoggedInObj;

    public function __construct($conn, $video, $userLoggedInObj)
    {
        $this->conn = $conn;
        $this->video = $video;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create()
    {
        return $this->createCommentSection();
    }

    private function createCommentSection()
    {
        $numComments =  $this->video->getNumberOfComment();
        $postedBy = $this->userLoggedInObj->getUserName();
        $videoId = $this->video->getId();
        $profileButton = ButtonProvider::createUserProfileButton($this->conn, $postedBy);
        $commentAction = "postComment(this, \"$postedBy\", $videoId, null, \"comments\")";
        $commentButton = ButtonProvider::createButton('COMMENT', null, $commentAction, 'postComment');
        $comments = $this->video->getComments();
        $commentItems = "";
        foreach ($comments as $comment){
            $commentItems .= $comment->create();
        }
        return "<div class='commentSection'>
                    <div class='header'>
                        <span class='commentCount'>$numComments Comments</span>
                        <div class='commentForm'>
                            $profileButton
                            <textarea class='commentBodyClass' placeholder='Add a public comment.'></textarea>
                            $commentButton
                        </div>
                    </div>
                    <div class='comments'>
                    $commentItems
                    </div>
                </div>";
    }
}

?>