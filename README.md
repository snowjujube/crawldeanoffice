# crawldeanoffice
正方教务系统爬虫
<h1 id="toc_0">PHP Curl + Simple HTML Dom 把母校教务处放进自己的服务器</h1>

<h2 id="toc_1">概述</h2>

<ul>
<li><code>CURL</code>的应用</li>
<li>正方教务Web请求分析</li>
<li>需要的Web请求的类的创建</li>
<li>教务验证码识别</li>
<li><code>Simple HTML Dom</code> 处理<code>CURL</code>到的<code>HTML</code></li>
</ul>

<hr/>

<h2 id="toc_2"><code>CURL</code>的应用</h2>

<ol>
<li><p>PHP <code>CURL</code>的介绍<br></p>

<p><code>CURL</code>是PHP中一个库。这个库可以帮助你可以通过URL与不同类型的服务器进行通讯，支持众多<code>运输层</code>/<code>网络层</code>协议。可能笔者本人并没有太多对<code>CURL</code>的深入了解，因此可能不能详细介绍，日后会好好深入学习<code>CURL</code>库。不过我从<code>Segmentfault</code>找到了一段介绍，把URL Copy给大家，有想深入了解的朋友可以去看看.</p>

<blockquote>
<p>PHP supports libcurl, a library created by Daniel Stenberg, that allows you to connect and communicate to many different types of servers with many different types of protocols. libcurl currently supports the http, https, ftp, gopher, telnet, dict, file, and ldap protocols. libcurl also supports HTTPS certificates, HTTP POST, HTTP PUT, FTP uploading (this can also be done with PHP&#39;s ftp extension), HTTP form based upload, proxies, cookies, and user+password authentication.</p>
</blockquote>

<p><a href="https://segmentfault.com/a/1190000006220620">https://segmentfault.com/a/1190000006220620</a></p></li>
<li><p><code>CURL</code>的配置</p>

<p>macOS上好像没有什么需要配置的内容。我使用<code>MAMP Pro</code>的环境以及<code>Mac</code>本身对PHP的支持，我没有做任何配置便已可以使用。各位使用Windows的朋友可以上Google或者Baidu去查查具体应该怎么配置，在此不做详细叙述。</p></li>
<li><p>我们即将用到的<code>CURL</code>类的创建<br></p>

<p>类中的这些方法其实也并不复杂，大家先稍作浏览。</p>

<pre><code>class curl
{
public function curl_request($url,$post=&#39;&#39;,$referer=&#39;&#39;){
    $curl = curl_init();
        //初始化curl
    curl_setopt($curl,CURLOPT_URL,$url);
        //设置CURLOPT_URL
    curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER[&quot;HTTP_USER_AGENT&quot;]);
        //设置user agent，设置为浏览器默认的user agent
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,0);
        //设置默认允许重定向
    curl_setopt($curl,CURLOPT_AUTOREFERER,1);
        //当返回的信息头含有转向信息时,自动设置前向连接
    curl_setopt($curl,CURLOPT_REFERER,&quot;http://202.200.206.54/xs_main.aspx?xh=&quot;.$referer);
        //设置referer
    if ($post){
        curl_setopt($curl,CURLOPT_POST,1);
        //如果有Post数据，则开启Post
        curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($post));
        //设置查询Post数据
    }
    curl_setopt($curl,CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //以文件流形式返回而不是直接输出
    $data = curl_exec($curl);
    //执行查询
    if (curl_errno($curl)) {
        return curl_error($curl);
        //如果有错返回错误并终止操作
    }
    curl_close($curl);
    return $data;
}
}
</code></pre>

<p>在该类中我声明了一个<code>curl_request()</code>方法，正方教务好像现在有两种访问方式。第一种是通过登陆返回<code>Cookie</code>串来完成本次访问操作的方式；第二种则是通过一个随机目录（后边会讲到）以及创建名为<code>ViewState</code>的隐藏域的方式。</p>

<p>第一种方式其实只需要在<code>curl_request()</code>中加入<code>Cookie</code>的<code>curl_setopt</code>即可；第二种方式则相对复杂一点，我数学不太好，借助网上大神的帮助找到了一个创建<code>ViewState</code>的方法，有能看懂的朋友可以私我e-mail: <a href="mailto:jung.jessica@outlook.com">jung.jessica@outlook.com</a>.</p></li>
</ol>

<pre><code>    public function viewStage($inputurl,$result){
        $url = $inputurl;
        $pattern = &#39;/&lt;input type=&quot;hidden&quot; name=&quot;__VIEWSTATE&quot; value=&quot;(.*?)&quot; \/&gt;/is&#39;;
        preg_match_all($pattern, $result, $matches);
        $res = $matches[1][0];
         // $pattern = &#39;/&lt;input type=&quot;hidden&quot; name=&quot;__VIEWSTATEGENERATOR&quot; value=&quot;(.*?)&quot; \/&gt;/is&#39;;
        //  preg_match_all($pattern, $result, $matches);
       // $res[1] = $matches[1][0];
        return $res;
    }
</code></pre>

<p>由于和数据爬虫有关，我把它也归类在了Curl类中。</p>

<h4 id="toc_3">总结</h4>

<p>通过<code>CURL</code>请求函数的定义和简单的类的创建，可能我们还不能直观的感受到他的强大和方便之处，于是我想试着做一个查分数的Web应用，开始研究如何爬进学校的教务处（学校保密<small style="color:darkgrey"> 非985/211😑</small>）。</p>

<hr/>

<h2 id="toc_4">正方教务系统的Web请求分析</h2>

<h4 id="toc_5">分析HTTP请求的APP</h4>

<p>我使用的是<code>macOS</code>上一款名为<code>Charles</code>的数据抓包软件，谷歌Chrome浏览器<code>Inspect</code>中的<code>Network</code>功能应该也能完成类似的抓包操作。</p>

<p><img src="media/15170446927157/charles.png" alt="charles"/>￼</p>

<p>大家可以根据需求自行选择喜欢的数据抓包软件。</p>

<h4 id="toc_6">分析登陆的URL</h4>

<p>首先我们来到教务处这个丑陋的界面。<br/>
<img src="media/15170446927157/15170730048240.jpg" alt=""/>￼</p>

<p>全国大多数高校应该都在用这套系统吧。然后我们将目光转移到地址栏上，观察一下当前我们访问的URL。</p>

<p><img src="media/15170446927157/15170731435829.jpg" alt=""/>￼</p>

<p>出于隐私不泄漏学校教务的地址。但是多次访问后我发现，每一次进入教务系统，我都会被分配一个随机24位的路径。我试着在网上找了很多种方式破译都了了收场。</p>

<p>如果我不能破译，那干脆直接利用这个地址好啦💁🏻。</p>

<p>以下代码可以查询到我们所需要的那个每次访问教务生成的随机路径，正是根据这个随机路径才保证了此次查询是由当前用户所进行的，我们正是要通过爬虫来模仿这样的操作。</p>

<pre><code>&lt;?PHP
    $url = &quot;http://就不告诉你&quot;;
    $headers = get_headers($url, TRUE);
    $url = $headers[&quot;Location&quot;];
    $result = array();
    preg_match_all(&quot;/(?:\()(.*)(?:\))/i&quot;,$url, $result);
    $_SESSION[&quot;fuckingcode&quot;] = $result[1][0];
?&gt;
</code></pre>

<p>我为URL使用<code>get_headers()</code>方法，第二个参数是返回数组的类型（0为索引数组，1为关联数组）。在返回的数组中取其中的<code>[&quot;Location&quot;]</code>，正好就是每次访问需要的随机路径啦，在正则匹配后得到一个我们希望的结果，加入<code>$_SESSION</code>中。<br/>
<img src="media/15170446927157/15170737504091.jpg" alt=""/>￼</p>

<p>至此，URL的分析算是大功告成了。</p>

<hr/>

<h2 id="toc_7">需要的Web请求的类的创建</h2>

<h4 id="toc_8">分析登陆时用到的GET/POST请求</h4>

<p>做一次对教务系统的登陆操作并在Charles中检索后，我们便可以清楚的得到访问一次教务系统需要的请求。</p>

<p><img src="media/15170446927157/15170740239627.jpg" alt=""/>￼</p>

<p>且不说验证码，我们先看一下做一次登陆向<code>Default.aspx</code>中的请求内容。</p>

<p><img src="media/15170446927157/15170741238909.jpg" alt=""/>￼</p>

<p><img src="media/15170446927157/15170741597529.jpg" alt=""/>￼</p>

<p>两张图片已经可以说明问题，登陆表单的验证采用了<code>Post</code>的方式，<code>Post</code>请求到<code>Default.aspx</code>页面。</p>

<p>需要提交的数据刚刚我们在<code>CURL</code>类中做了这样的参数设置：</p>

<pre><code> if ($post){
            curl_setopt($curl,CURLOPT_POST,1);
            //如果有Post数据，则开启Post
            curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($post));
            //设置查询Post数据
        }
</code></pre>

<p><code>Post</code>的默认值为空，但是如果有表单需要提交，开启<code>CURLOPT_POST</code>，并设置做一次标准的<code>HTTP</code><code>POST</code>请求，只需要一个参数的设置，真的方便。<code>AJAX</code>无法进行跨域请求，局限性可想而知，而<code>CURL</code>库为PHP真的带来了无限的乐趣。</p>

<p>而提交的表单参数也非常清晰。<code>__VIEWSTATE</code>刚刚的<code>CURL</code>类中利用正则表达式帮我们自动生成。我们需要提供给生成<code>__VIEWSTATE</code>的参数是当前访问的<code>URL</code>以及<code>CURL</code>请求当前URL一次的结果。</p>

<p>其余的参数则一目了然：</p>

<table>
<thead>
<tr>
<th>KEY</th>
<th>VALUE</th>
</tr>
</thead>

<tbody>
<tr>
<td>txtUserName</td>
<td>学生学号</td>
</tr>
<tr>
<td>Textbox1</td>
<td>密码的一个隐藏域（教务为了安全性设置，其实很愚蠢）</td>
</tr>
<tr>
<td>TextBox2</td>
<td>密码域 验证发现Textbox1 和 Textbox2 都输入密码时方能正确提交</td>
</tr>
<tr>
<td>txtSecretCode</td>
<td>验证码（暂时不考虑）</td>
</tr>
<tr>
<td>RadioButtonList1</td>
<td>这里是GB2312编码导致的乱码 因此提交时这里应填GB2312编码的”学生“</td>
</tr>
<tr>
<td>Button1</td>
<td>同上，GB2312编码的“登陆二字”</td>
</tr>
<tr>
<td>lbLanguage</td>
<td>留空</td>
</tr>
<tr>
<td>hidPdrs</td>
<td>留空</td>
</tr>
<tr>
<td>hidsc</td>
<td>留空</td>
</tr>
</tbody>
</table>

<p>分析完这一切，我们便可以在进行一些设置后，开始一次愉快的本地教务处登陆行为了。</p>

<p>为了方便查询，我根据上述表单创建了一个<code>Userinfo</code>类。代码如下：</p>

<pre><code>&lt;?php
/**
 * Created by PhpStorm.
 * User: snowjujube
 * Date: 24/01/2018
 * Time: 10:28 PM
 */

class userinfo
{
    function loginpost($res,$str,$username,$password){
        $post[&#39;__VIEWSTATE&#39;] = &quot;$res&quot;;
        $post[&#39;txtUserName&#39;] = &quot;$username&quot;;
        $post[&#39;TextBox2&#39;] = &quot;$password&quot;;
        $post[&#39;TextBox1&#39;] = &quot;$password&quot;;
        $post[&#39;txtSecretCode&#39;] = &quot;$str&quot;;
        $post[&#39;lbLanguage&#39;] = &#39;&#39;;
        $post[&#39;RadioButtonList1&#39;] = iconv(&#39;utf-8&#39;, &#39;gb2312&#39;, &#39;学生&#39;);
        $post[&#39;Button1&#39;] = iconv(&#39;utf-8&#39;, &#39;gb2312&#39;, &#39;登录&#39;);
        return $post;
    }
}
</code></pre>

<p>四个参数分别为<code>CURL</code>类中生成的viewstate的绑定，验证码的字符串，学号，密码。</p>

<p>将生成好的请求内容返回即可。</p>

<p>在做一次请求前，我们还缺少最后一样重要的事情要做：匹配验证码。</p>

<h2 id="toc_9">教务验证码识别</h2>

<h4 id="toc_10">两种解决方案</h4>

<p>之所以把验证码的处理留在最后，我找到了两种关于验证码处理的解决方案。</p>

<pre><code>1. 手动输入
2. Github上大佬写好的验证码识别插件
</code></pre>

<p>第一种方式是传统的教务查询方式，但显然大家没有人喜欢传统的输入验证码的累赘的方式，但这种方式除非教务处崩溃，通过我们自己服务器访问教务处的成功率可以是100%；第二种方式的缺陷就在于AI图像识别验证码，成功率在2/3左右（顺利的话一次就能成功，我也在验证过程中遇到过连续十次失败的情况）。@git:<a href="https://github.com/Kuri-su/CAPTCHA_Reader_by_zhengfang">https://github.com/Kuri-su/CAPTCHA_Reader_by_zhengfang</a> ，大家如果有什么更好的方法也可以私我。</p>

<h4 id="toc_11">一些在模拟登陆过程中需要了解的注意事项。</h4>

<ol>
<li>保证此次所有操作在同样的分配好的随机目录中，这样才可以保证登陆以及后续比如查成绩查课表的操作的准确。</li>
<li>一定要使用UTF-8编码。</li>
<li>验证登陆是否成功可以通过正则来实现。</li>
<li>本地服务器支持<code>CURLOPT_FOLLOWLOCATION</code>，也就意味着假许我们的请求成功，服务器返回的数据是一个要跳转的页面，我们也可以爬到当前页面并通过<code>CURLOPT_RETURNTRANSFER</code>的打开来返回文件流操作；而部分云服务器无法打开<code>CURLOPT_FOLLOWLOCATION</code>。</li>
</ol>

<h4 id="toc_12">一次完整的localhost登陆过程</h4>

<pre><code>&lt;?
session_start();
//开启session
header(&quot;Content-Type:text/html;charset=gb2312&quot;);
//注意GB2312编码
    include_once &quot;fuckingcode.php&quot;;
    //包含刚刚生成的随机目录，将其加入session
    require_once &quot;curl.php&quot;;
    //刚刚创建的curl类    
    require_once &quot;userinfo.php&quot;;
  //同 刚刚创建的Post请求类  
    require_once &quot;zhengfang/PIN Identify by fangzheng.php&quot;;
  //验证码识别插件
  
     $status = 0;
     //如果查询成功将status置1
    $curl = new curl();
    $userinfo = new userinfo(); 
    $username = $_GET[&quot;usr&quot;];
    $password = $_GET[&quot;pwd&quot;];
    //获取接收到的请求
    $login_url = &quot;http://I am a good boy/({$_SESSION[&quot;fuckingcode&quot;]})/Default2.aspx&quot;;
    //echo $login_url;
    $login_referer = &quot;http://I love my college/xs_main.aspx?xh=$username&quot;; 
    //设置需要爬虫的url和重定向referer的url
    $str = $_SESSION[&quot;letteri&quot;];
    $str .= $_SESSION[&quot;letterii&quot;];
    $str .= $_SESSION[&quot;letteriii&quot;];
    $str .= $_SESSION[&quot;letteriv&quot;];
    //愚蠢的设置验证码
    $res_login = $curl-&gt;viewStage($login_url, $login_result);
      //设置VIEWSTATE参数
    $post_login = $userinfo-&gt;loginpost($res_login, $str, $username, $password);
    //设置一下即将post的内容
    $main = $curl-&gt;curl_request($login_url, $post_login, $username);
    //进行一次完整的登陆操作
    $mainhtml = str_get_html($main);
    //这里是用到了`Simple HTML Dom` 处理`CURL`到的`HTML`
    foreach ($mainhtml-&gt;find(&quot;a&quot;) as $item){
        $name = $item-&gt;plaintext;
    }
    if ($name == &quot;here&quot;) {
        $status = 1;
        $len = strlen($name[1]) / 2;
        $user = mb_substr($name[1], 0, $len - 2, &quot;GB2312&quot;);
        $urlname = urlencode($user);
    }
    //这里判断是否登陆成功，若成功status置1
    
?&gt;
</code></pre>

<h4 id="toc_13">总结</h4>

<p>一次完整的本地登陆操作也不过如此，但是登陆成功后我们虽然可以看到页面加载的内容或者提示请求你跳转到新页面的<code>Object</code>，但当我们重新定向的时候，会出现404 not found 的错误，这是因为如果我们想跳转页面，那便是一次新的请求（比如需要查成绩或者课表），请求的内容不一样，那么我们只要在抓包的过程中去仔细看看如果我们想要得到自己在教务系统中想执行的操作应该怎么办就好，照猫画虎是每个人最喜欢的事情。</p>

<h2 id="toc_14"><code>Simple HTML Dom</code> 处理<code>CURL</code>到的<code>HTML</code></h2>

<h4 id="toc_15">使用场景</h4>

<p>这里假设我已经取到了一个完整的<code>HTML</code>页面。<br/>
<code>CURL</code>返回的是一个长长的<code>String</code>，操作起来可以说是非常它妈的苦难了😣。Git上有一个叫做<code>Simple HTML Dom</code>的插件，不仅可以遍历HTML还可以遍历url，大家可以去试试看。我这里做一个简单的介绍。</p>

<h4 id="toc_16">一次完整的爬成绩操作</h4>

<p>定义查询成绩的返回完整<code>Post</code>请求的函数：</p>

<pre><code>    function scorepost($res){
        $post[&quot;__EVENTTARGET&quot;] = &quot;&quot;;
        $post[&quot;__EVENTARGUMENT&quot;] = &quot;&quot;;
        $post[&quot;__VIEWSTATE&quot;] = &quot;$res&quot;;
        $post[&quot;hidLanguage&quot;] = &quot;&quot;;
        $post[&quot;ddlXN&quot;] = &quot;2017-2018&quot;;
        $post[&quot;ddlXQ&quot;] = &quot;1&quot;;
        $post[&quot;ddl_kcxz&quot;] = &quot;01&quot;;
        $post[&quot;btn_zcj&quot;] = iconv(&#39;utf-8&#39;, &#39;gb2312&#39;, &#39;历年成绩&#39;);
        return $post;
    }
</code></pre>

<p>一次完整的查询过程：</p>

<pre><code>$info_url = &quot;http://Never told you my babe/({$_SESSION[&quot;fuckingcode&quot;]})/xscjcx.aspx?xh=$username&amp;xm=$urlname&amp;gnmkdm=N121623&quot;;
    $info_result = $curl-&gt;curl_request($info_url);
    $res_info = $curl-&gt;viewStage($info_url, $info_result);
    $post_info = $userinfo-&gt;scorepost($res_info);
    $info = $curl-&gt;curl_request($info_url, $post_info, $login_referer);
    preg_match(&#39;/&lt;span id=\&quot;lbl_zymc\&quot;&gt;(.*)&lt;\/span&gt;/&#39;, $info, $label);
//print_r($label);
    if ($label == &quot;&quot;) {
        $status = 0;
    }
</code></pre>

<p>使用插件遍历<code>html</code>:</p>

<pre><code>    foreach ($html-&gt;find(&quot;table#Datagrid1&quot;) as $table) {
        foreach ($table-&gt;find(&quot;tr&quot;) as $k =&gt; $tr) {
            $score[$k][&#39;year&#39;] = $tr-&gt;find(&#39;td&#39;, 0)-&gt;plaintext;//学年
            $score[$k][&#39;term&#39;] = $tr-&gt;find(&#39;td&#39;, 1)-&gt;plaintext;//学期
            $score[$k][&#39;code&#39;] = $tr-&gt;find(&#39;td&#39;, 2)-&gt;plaintext;//课程编号
            $score[$k][&#39;name&#39;] = $tr-&gt;find(&#39;td&#39;, 3)-&gt;plaintext;//课程名
            $score[$k][&#39;nature&#39;] = $tr-&gt;find(&#39;td&#39;, 4)-&gt;plaintext;//课程性质
            $score[$k][&#39;credit&#39;] = $tr-&gt;find(&#39;td&#39;, 6)-&gt;plaintext;//学分
            $score[$k][&#39;point&#39;] = $tr-&gt;find(&#39;td&#39;, 7)-&gt;plaintext;//绩点
            $score[$k][&#39;first_score&#39;] = $tr-&gt;find(&#39;td&#39;, 8)-&gt;plaintext;//成绩
            $score[$k][&#39;second_score&#39;] = $tr-&gt;find(&#39;td&#39;, 10)-&gt;plaintext;//补考成绩
            $score[$k][&#39;third_score&#39;] = $tr-&gt;find(&#39;td&#39;, 11)-&gt;plaintext;//重修成绩
            $score[$k][&#39;studentid&#39;] = $username;
        }
    }
    unset($score[0]);
</code></pre>

<p>我们便可以这样轻轻松松把教务处搬到我们自己的服务器，再稍稍做一些前端，一个第三方成绩查询功能也就实现了。</p>

<p>最后附上张成果图：</p>

<p><img src="media/15170446927157/15170780427968.jpg" alt=""/>￼</p>

<p>不要吐槽学渣</p>
