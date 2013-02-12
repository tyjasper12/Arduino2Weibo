<!DOCTYPE HTML> 
<html lang="zh-CN"> 
<head> 
<meta charset="UTF-8"> 
<title>Arduino2Weibo - 可以用围脖远程控制Arduino啦!</title> 
<link rel="stylesheet" type="text/css" href="style.css" media="all" />  
</head>  
<body> 
	<h1>Arduino围脖<sup title="Version 0.3">v0.3</sup></h1> 
	<h2>可以用围脖远程控制Arduino啦!</h2> 
	<div>
        <br/>
	<p><a href="../index.php">< 返回首页</a></p>
		<h3>实例1: Arduino发围脖</h3> 
		
            <p>让<strong>Arduino</strong>通过新浪围脖提供的<a href="http://open.weibo.com/" target="_blank">API</a>发送围脖。您需要有一块<strong>Arduino</strong>和一块<strong>W5100</strong>核心的<a href="http://www.arduino.cc/en/Main/ArduinoEthernetShield"  target="_blank">Ethernet Shield</a>。</p>
            <h4>服务器端脚本说明</h4>
            <p>Arduino围脖的服务器脚本托管于<a href="http://sae.sina.com.cn/"  target="_blank">新浪SAE</a>上,负责Arduino和围脖API之间的数据处理。之前版本的脚本代码就是<a href="http://sae.sina.com.cn/?m=apps&a=detail&aid=6"  target="_blank">新浪微博OAuth框架</a>。新版增加了远程控制功能后将服务器端脚本开源：<a href="https://github.com/naozhendang/Arduino2Weibo" target="_blank">Arduino2Weibo服务器端脚本</a>。出于安全和云豆消耗方面的考虑，我们鼓励您在SAE上架设自己的Arduino服务器。</p> 
	    <div class="panel">
            <p>如果您使用自己假设的服务，请将一会儿下载的Arduino2Weibo.cpp中的</p>
            <pre>#define LIB_DOMAIN "arduino2weibo.sinaapp.com"</pre>
            <p>修改为</p>
            <pre>#define LIB_DOMAIN "您的服务器地址"</pre>
            </div>
                
                <h3>具体步骤</h3>
		<ul>
			<li><strong>第一步</strong>: 连接好Arduino,Ethernet Shield,网线和您的电脑。</li>
			<li><strong>第二步</strong>: 安装扩展程序库</li>
				<div class="panel" id="step2">
					<ul> 
						<li>1.下载<a href="https://github.com/naozhendang/Arduino2Weibo" target="_blank">Arduino2Weibo扩展库</a>，解压后将Arduino2Weibo文件夹复制到Arduino目录的libraries文件夹里(..\Arduino\libraries\)。</li>
						<li>2.(如果您使用的是Arduino IDE 1.0及以后版本,可以跳过此步,IDE 1.0以后内置了DNS库)到<a href="http://gkaindl.com/software/arduino-ethernet"  target="_blank">gkaindl.com</a>下载EthernetDNS扩展库并解压安装。</li>
						<li>3.重启Arduino IDE</li>
						<li>* 了解更多扩展库知识，可以<a href="http://www.arduino.cc/en/Hacking/Libraries" class="ext"  target="_blank">点这里</a>.</li>
					</ul> 
				</div>
			<li><strong>第三步</strong>: 让Arduino围脖一下吧!</li>
				<div class="panel" id="step3">
					 <p>您可以在File > Examples> Arduino2Weibo > SimplePost中导入此段代码。不要忘了替换"YOUR-USERNAME-HERE"和"YOUR-PASSWORD-HERE"两个值!!!</p>
					 <p>*如发送失败，您可以打开Serial Monitor查看返回具体的错误信息。</p>
					 
<pre>
#if defined(ARDUINO) && ARDUINO > 18   // Arduino 0019 or later
#include &lt;SPI.h&gt;
#endif
#include &lt;Ethernet.h&gt;
//#include &lt;EthernetDNS.h&gt;  //Only needed in Arduino 0023 or earlier
#include &lt;Arduino2Weibo.h&gt;
// Ethernet Shield 设置
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// 配置Arduino的局域网IP地址
// 请确保IP没有防火墙限制、没有和局域网内其他IP冲突
byte ip[] = { 192, 168, 1, 106 };

// 绑定围脖的密码和用户名
Weibo weibo("YOUR-USERNAME-HERE","YOUR-PASSWORD-HERE");

// Message to post
// 由于Arduino IDE不支持敲中文和一些特殊字符，我做了个编码工具 
// 请访问 http://arduino2weibo.sinaapp.com/encode.php
char msg[] = "Hello Weibo, I'm Arduino!";

void setup()
{
  delay(1000);
  Ethernet.begin(mac, ip);
  Serial.begin(9600);
  
  Serial.println("Connecting ...");
  if (weibo.post(msg)) {
    //Read return json data
     char* json = weibo.return_data(); 
     
     if(json){
        //Error check
        char* error = weibo.value_pointer("error",json);
        
        if(!error){
            //If it succeeds, we could find the post id
            char* id = weibo.value_pointer("id",json);           
            if(id){
              Serial.println("Done");
            } else {
              Serial.println("Unknown error");
            }
        } else {
            //Print out the error
            for(int i= 0; i < weibo.value_length(error); i++){
              Serial.print(error[i]);
            }
            Serial.println();
        }
     } else {
       Serial.println("No return data from Weibo API");
     };

     //*******Important***********
     //Since we used malloc() in return_data()
     //We need to free() the memory
     free(json);
     json = 0;
        
  } else {
    Serial.println("Connection failed.");
  }
  
}

void loop()
{
}
</pre>

					 
					 
					 
					 
				</div>	
                                <li><strong>第四步</strong>: Upload! 稍等一会儿您就可以看到Arduino发的围脖了！</li>
		</ul>
 <br/> <p><a href="../index.php">< 返回首页</a></p>              
<br/>
	</div>		
	<div id="footer"> 
		2011 -2012 &copy; Arduino2Weibo Project</a> 
	</div> 
</body> 
</html> 