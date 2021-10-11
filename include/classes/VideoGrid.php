<?php

class VideoGrid
{
    private $conn, $userLoggedInObj;
    private $largeMode = false;
    private $gridClass = "videoGrid";
    public  function __construct($conn, $userLoggedInObj){
        $this->conn = $conn;
        $this->userLoggedInObj = $userLoggedInObj;
    }
    public function create($videos, $title, $showFilter){
        if ($videos == null){
            $gridItems = $this->generateItems();
        }else{
            $gridItems = $this->generateItemsFromVideos($videos);
        }
        $header = "";
        if ($title != null){
            $header = $this->createGridHeader($title, $showFilter);
        }
        return "$header
                <div class='$this->gridClass'>
                    $gridItems
                </div>";
    }
    public function generateItems(){
        $query = $this->conn->prepare("SELECT * FROM videos ORDER BY rand() LIMIT 15");
        $query->execute();
        $elementHtml = "";
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
            $video = new Video($this->conn, $row, $this->userLoggedInObj);
            $item = new VideoGridItem($video, $this->largeMode);
            $elementHtml .= $item->create();
        }
        return $elementHtml;
    }
    public function generateItemsFromVideos($videos){
        $elementHtml = "";
        foreach ($videos as $video){
            $item = new VideoGridItem($video,$this->largeMode);
            $elementHtml .= $item->create();
        }
        return $elementHtml;
    }
    public function createGridHeader($title, $showFilter){
        $filter = "";
        if ($showFilter){
            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $urlArray  = parse_url($url);
            $query = $urlArray['query'];
            parse_str($query, $params);
            unset($params['orderBy']);
            $newQuery = http_build_query($params);
            $newUrl = basename($_SERVER["PHP_SELF"]). '?' . $newQuery;
            $filter = "<div class='right'>
                        <span>Order By:</span>
                        <a href='$newUrl&orderBy=uploadDate'>Upload Date</a>
                        <a href='$newUrl&orderBy=views'>Most Viewed</a>
                       </div>";

        }
        return "<div class='videoGridHeader'>
                   <div class='left'>$title</div>
                   $filter
                </div>";
    }
    public function createLarge($videos, $title, $showFilter){
        $this->gridClass .= ' large';
        $this->largeMode = true;
        return $this->create($videos, $title, $showFilter);
    }
}