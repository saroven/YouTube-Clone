<?php
require 'ButtonProvider.php';
require 'CommentControls.php';
class Comment
{
    private $conn, $sqlData, $userLoggedInObj, $videoId;
    public function __construct($conn, $input, $userLoggedInObj, $videoId)
    {
        $this->conn = $conn;
        $this->userLoggedInObj = $userLoggedInObj;
        $this->videoId = $videoId;

        if (is_array($input)){
            $this->sqlData = $input;
        }else{
            $query = $this->conn->prepare("SELECT * FROM comments WHERE id=:id");
            $query->bindParam(":id", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }


    }
    public function create()
    {
        $body = $this->sqlData['body'];
        $postedBy = $this->sqlData['postedBy'];
        $profileButton = ButtonProvider::createUserProfileButton($this->conn, $postedBy);
        $timespan = ""; //to get timespan

        $commentControlsObj = new CommentControls($this->conn, $this, $this->userLoggedInObj);
        $commentControl = $commentControlsObj->create();
        return "<div class='itemContainer'>
                    <div class='comment'>
                        $profileButton
                        <div class='mainContainer'>
                            <div class='commentHeader'>
                                <a href='profile.php?username=$postedBy'>
                                    <span class='username'>$postedBy</span>
                                </a>
                                <span class='timestamp'>$timespan</span>
                            </div>
                            <div class='body'>
                                $body
                            </div>
                        </div>
                    </div>
                    $commentControl
                </div>";
    }


    public function getId()
    {
        return $this->sqlData['id'];
    }
    public function getVideoId()
    {
        return $this->videoId;
    }
    public function wasLikedBy(){
        $username = $this->userLoggedInObj->getUserName();
        $id = $this->getId();
        $query = $this->conn->prepare("SELECT * FROM likes WHERE username=:username AND commentId=:commentId");
        $query->bindParam(":username", $username);
        $query->bindParam(":commentId", $id);
        $query->execute();
        return $query->rowCount() > 0;
    }
    public function wasDislikedBy(){
        $username = $this->userLoggedInObj->getUserName();
        $id = $this->getId();
        $query = $this->conn->prepare("SELECT * FROM dislikes WHERE username=:username AND commentId=:commentId");
        $query->bindParam(":username", $username);
        $query->bindParam(":commentId", $id);
        $query->execute();
        return $query->rowCount() > 0;
    }
    public function getLikes(){
        $query = $this->conn->prepare("SELECT count(*) as count FROM likes WHERE commentId=:commentId");
        $commentId = $this->getId();
        $query->bindParam(":commentId", $commentId);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);
        $numLikes = $data["count"];


        $query = $this->conn->prepare("SELECT count(*) as count FROM dislikes WHERE commentId=:commentId");
        $commentId = $this->getId();
        $query->bindParam(":commentId", $commentId);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);
        $numDisLikes = $data["count"];

        return $numLikes - $numDisLikes;
    }
}