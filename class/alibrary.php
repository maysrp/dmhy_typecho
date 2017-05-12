<?php
	class alibrary{
		public $db;
		public function __construct(){
			$this->db = new medoo([
    				'database_type' => 'mysql',
 				    'database_name' => DB_NAME,
 				    'server' => 'localhost',
 			        'username' => DB_USER,
  				    'password' => DB_PASSWORD,
			        'charset' => 'utf8',
   			 		'port' => 3306,
    				'prefix' => 'typecho_',
			]);

		}
		public function index(){
			$count=$this->count();
			$this->sxp_index($count);
		} 
		protected function sxp_index($count){  
        	phpQuery::newDocumentFile("https://share.dmhy.org/topics/list/page/".$count); 
        	$exx=pq("tr"); 
        	foreach ($exx as $value) {
            	$info['magnet']=pq($value)->find(".download-arrow")->attr("href");//magnet
            	if(!$info['magnet']){
                	continue;
            	}
            	$info['zname']=trim(pq($value)->find(".title a:eq(0)")->text());//字幕组*******
            	$info['fname']=trim(pq($value)->find(".title a:eq(1)")->text());//文件名 a attr("href")地址***********
            	if(!$info['fname']){
                	$info['fname']=$info['zname'];
                	$info['zname']="";
                	$info['url']=pq($value)->find(".title a:eq(0)")->attr("href");//文件名 a attr("href")地址
            	}else{
                	$info['url']=pq($value)->find(".title a:eq(1)")->attr("href");//文件名 a attr("href")地址
            	}
            	$info['ctime']=pq($value)->find("td:first span ")->text();//time ************
            	$info['ntype']=trim(pq($value)->find("td:eq(1)")->text());//类型 ****************
            	$info['size']=pq($value)->find("td:eq(4)")->text();//大小
            	$info['download']=pq($value)->find("td:eq(6)")->text();//下载
            	$info['cdownload']=pq($value)->find("td:eq(7)")->text();//完成
            	if(!$this->jugg($info['fname'])){//不存在插入
            		$url="https://share.dmhy.org".$info['url'];
            		$info['post']=$this->sxp_cont($url);//帖子内容 ****************
            		$this->insert_info($info);
            	}
        	}
    	}
    	protected function sxp_cont($url){
        	phpQuery::newDocumentFile($url); 
        	$info=pq(".topic-nfo")->html(); 
        	$file_list=pq(".file_list")->html();
        	$magnet=pq("#magnet2")->html();
        	return $info."<hr><blockquote style=\"background-color:#FAFAFA;border-radius:10px;padding:5px;\"><h3>下载地址:<a href=\"".$magnet."\">".$magnet."</a></h3><h3>文件列表</h3>".$file_list."</blockquote>";
    	}
    	protected function insert_info($info){
        	$time=time();
        	$add['title']=$info['fname'];
        	$add['text']=$info['post'];
        	$add['created']=$time;
        	$add['allowComment']=1;
        	$add['allowFeed']=1;
       		$add['type']='post';
       		$add['status']='publish';
       		$add['authorId']=2;
       		$cid=$this->db->insert('contents',$add);
       		$mid=$this->meats($info['ntype']);
       		$this->relationships($cid,$mid);
    	}
    	protected function meats($name){
    		switch ($name) {
            	case '動畫':
                	return 5;
                	break;
            	case '季度全集':
                	return 6;
                	break;
            	case '流行音樂':
            	case '動漫音樂':
            	case '音樂':
            	case '同人音樂':
                	return 8;
                	break;
            	case '漫畫':
            	case '港台原版':
            	case '日文原版':
                	return 7;
                	break;
            	case '日劇':
                	return 9;
                	break;
            	case '遊戲':
            	case '電腦遊戲':
            	case '遊戲周邊':
                	return 10;
                	break;
                case 'RAW':
                	return 12;
                	break;
            	default:
                	return 13;
                	break;
        	}
    	}
    	protected function relationships($cid,$mid){
    		//cid文章 mid meats的ID
    		$add['cid']=$cid;
    		$add['mid']=$mid;
    		$this->db->insert('relationships',$add);
    	}
    	protected function jugg($name){
    		$where['title']=$name;
    		return $this->db->select('contents','cid',$where);//返回是否已经采集
    	}
    	protected function count(){
    		$this->add_count();
    		$where['id[>]']=0;
    		return $this->db->count('count',$where);
    	}
    	protected function add_count(){
    		$add['time']=time();
    		$this->db->insert('count',$add);
    	}

	}