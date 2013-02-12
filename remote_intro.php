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
		<h3>实例2: 围脖远程控制</h3> 
		
            <p>发围脖<strong>@Arduino绑定的围脖账号</strong>，就可以用特定的命令行控制<strong>Arduino</strong>设备。您可以用围脖让<strong>Arduino</strong>开门，关灯，煮咖啡。。。只要您能想的到！本实例中，我们通过发送围脖@您的Arduino控制LED的开关(ON代表开，OFF代表关)。您需要有一块<strong>Arduino</strong>和一块<strong>W5100</strong>核心的<a href="http://www.arduino.cc/en/Main/ArduinoEthernetShield"  target="_blank">Ethernet Shield</a>。</p>
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
			<li><strong>第一步</strong>: 连接好Arduino,Ethernet Shield,网线和您的电脑。将LED的长脚(正极)跟220欧的电阻相连并连接pin9，短脚(负极)接GND。</li>
                        <div><img src="../img/ExampleCircuit_sch.png" /></div>
			<li><strong>第二步</strong>: 安装扩展程序库</li>
				<div class="panel" id="step2">
					<ul> 
						<li>1.下载<a href="https://github.com/naozhendang/Arduino2Weibo" target="_blank">Arduino2Weibo扩展库</a>，解压后将Arduino2Weibo文件夹复制到Arduino目录的libraries文件夹里(..\Arduino\libraries\)。</li>
						<li>2.(如果您使用的是Arduino IDE 1.0及以后版本,可以跳过此步,IDE 1.0以后内置了DNS库)到<a href="http://gkaindl.com/software/arduino-ethernet"  target="_blank">gkaindl.com</a>下载EthernetDNS扩展库并解压安装。</li>
						<li>3.重启Arduino IDE</li>
						<li>* 了解更多扩展库知识，可以<a href="http://www.arduino.cc/en/Hacking/Libraries" class="ext"  target="_blank">点这里</a>.</li>
					</ul> 
				</div>
			<li><strong>第三步</strong>: 下面是见证奇迹的时刻</li>
				<div class="panel" id="step3">
					 <p>您可以在File > Examples> Arduino2Weibo > RemoteControl中导入此段代码。不要忘了替换"YOUR-USERNAME-HERE","YOUR-PASSWORD-HERE","COMMAND-UID"的值!!!</p>
					 <p>*您可以打开Serial Monitor查看返回具体的错误信息。</p>
					 
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

// 定义回复的围脖消息
// 由于Arduino IDE不支持敲中文和一些特殊字符，我做了个编码工具 
// 请访问 http://arduino2weibo.sinaapp.com/encode.php
char msg_on[] = "Roger:ON";
char msg_off[] = "Roger:OFF";
char msg_unknown[] = "Unknown Command";

//设置个锚点，记录每次获取的消息的id值
char sid[20];

void setup()
{
  delay(1000);
  Ethernet.begin(mac, ip);
  Serial.begin(9600);
  
  //Ethernet Shield will use digital pin 13,12,11,10,2 and analog pin 0, 1
  //Let's use pin 9
  pinMode(9, OUTPUT);
}

void loop()
{  
  checkMen();
  delay(60000);
}

void checkMen(){
   
  Serial.println("Connecting ...");

  if (weibo.atme(sid)) {
  
     //Read the JSON data
     char* json = weibo.return_data();  
     
     if(json){
        //Error check
        char* error = weibo.value_pointer("error",json);
        
        if(!error){
            //Locate different values
            char* id = weibo.value_pointer("id",json);
            char* text = weibo.value_pointer("text",json);
            char* uid = weibo.value_pointer("uid",json);
            char* user = weibo.value_pointer("user",json);
			
	    //Store the id as an anchor
            int count = weibo.value_length(id);
            memcpy(sid,id,count);
            sid[count] ='\0';
            
            //匹配UID，您可以指定Arduino听从于某个用户或者多个用户，只要提供用户的UID
	    //UID怎么找: http://arduino2weibo.sinaapp.com/uid.php
            //当然也可以使用user值匹配用户名。
            if(weibo.compare_ids(uid,"COMMAND-UID")){ //Enter the UID sends commands.
			    
		//匹配命令行，可以匹配中文
		//中文编码工具 : http://arduino2weibo.sinaapp.com/encode.php
		if(weibo.find_substring(text,"ON")){
			//You can replace it with your own command
			digitalWrite(9, HIGH);
					
			//Free(json) before echo 
			//Since we used malloc() in return_data()
			free(json);
			json = 0;
					
			echo(id,msg_on);
                        Serial.println(msg_on);
		} else if (weibo.find_substring(text,"OFF")){
			//You can replace it with your own command
			digitalWrite(9, LOW);
					
			//Free(json) before echo 
			//Since we used malloc() in return_data()
			free(json);
			json = 0;
					
			echo(id,msg_off);
			Serial.println(msg_off);
		} else {
			//Unknown command
			//Free(json) before echo 
			//Since we used malloc() in return_data()
			free(json);
			json = 0;
					
			echo(id,msg_unknown);
			Serial.println(msg_unknown);
		}
            }
        } else {
            //Print out the error
            for(int i= 0; i < weibo.value_length(error); i++){
              Serial.print(error[i]);
            }
            Serial.println();
        }
     } else {
       Serial.println("No new @ for me");
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

//回复命令
void echo(const char *id, const char *msg){

  Serial.println("Echoing ...");
  
  if (weibo.repost(id,msg)) {
    //Read return json data
     char* json = weibo.return_data(); 
     
     if(json){
        //Error check
        char* error = weibo.value_pointer("error",json);
        
        if(!error){
            //If it succeeds, we could find the post id
            char* id = weibo.value_pointer("id",json);           
            if(id){
              Serial.println("Echo done");
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
       Serial.println("No return from Weibo API");
     };

     //*******Important***********
     //Since we used malloc() in return_data()
     //We need to free() the memory
     free(json);
     json = 0;
        
  } else {
    Serial.println("Echo failed");
  }

}
</pre>

					 
					 
					 
					 
				</div>	
                                <li><strong>第四步</strong>: Upload! 您可以@您的Arduino<strong>ON</strong>或者<strong>OFF</strong>控制LED灯了！</li>
		</ul>
 <br/> <p><a href="../index.php">< 返回首页</a></p>              
<br/>
	</div>		
	<div id="footer"> 
		2011 -2012 &copy; Arduino2Weibo Project</a> 
	</div> 
</body> 
</html> 