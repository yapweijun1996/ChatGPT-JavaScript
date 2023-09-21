<?php
// [start] allow gzip
header('Content-Encoding: gzip');
ob_start('ob_gzhandler');
// [end  ] allow gzip

// [start] Display All Message in php server
/*foreach ($_SERVER as $key => $value) {
    echo $key . " : " . $value . "<br>";
}*/
// [end  ] Display All Message in php server

// [start] Allow Cache 
// Set the timezone and enable caching
date_default_timezone_set('GMT');
header('Cache-Control: public');
header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 year')) . ' GMT');

// Check if the browser's cache is still valid.
$lastModified = filemtime(__FILE__); // check current file last modified time
$etag = md5_file(__FILE__); // create a md5 hash

header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
header("ETag: $etag");

// Implement conditional caching
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate("D, d M Y H:i:s", $lastModified) . ' GMT' ||
        trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag
    ) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
}
// [end  ] Allow Cache 
?>
<!DOCTYPE html>
<!-- 
Date        Mod By      Log
20230320    WeiJun      Enhancement for Chat History
20230531    WeiJun      Addon textarea focus after page load
20230531    WeiJun      Addon function detect_and_generate_clickable_link() use to check valid link and update it to clickable link
20230601    WeiJun      Fix bug in detect_and_generate_clickable_link, regex
20230608    WeiJun      Addon Temperature Select Option, Enhance Chat History, Addon Roles Prompt
20230708    WeiJun      Addon Chat GPT Model Select Option and default it as GPT 3.5
20230724    WeiJun      Enhance the interface
20230725    WeiJun      Addon new prompt
20230726    WeiJun      Enhance code output interface and copy code function
20230728    WeiJun      Addon Local Storage 
20230729    WeiJun      Allow Cache, gzip
20230815    WeiJun      Addon Model GPT4 32k
20230921    WeiJun      Addon Chat History Function

-->

<html>
  <head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="expires" content="Sat, 01 Jan 2050 1:00:00 GMT" />
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
      
    <title>Simple Chat GPT</title>
    <style>
    html{
        height: -webkit-fill-available;
        height: 100%;
    }
    body{
        margin: 0;
        height: 100%;
    }
	code.language-code{
		width: 100%;
		display: block;
        box-sizing: border-box;
        padding: 20px;
		padding-top: 40px;
		padding-bottom: 30px;
	}
	code.replied-message.language-code {
		background-color: #1f1f1f;
		color: #ccc;
		font-size: 14px;
		font-family: 'Courier New', Courier, monospace;
		overflow: auto;
		position: relative; /* used for positioning the copy button */
		border-radius: 15px;
	}

    button.code_copy_button {
	  position: relative;
	  left: -20px;
	  top: 37px;
	  background-color: #ff7f50;
	  color: #fff;
	  padding: 5px 10px;
	  border: none;
	  border-radius: 3px;
	  font-size: 14px;
	  cursor: pointer;
	  z-index: 1;
	  float: right;
	}

	button.code_copy_button:hover {
	  background-color: #ffa07a;
	}
        
    div.div_code{
        padding: 0px 10px;
    }


	.loading {
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  height: 20px;
	  background-color: #fff;
	}

	#dot1, #dot2, #dot3 {
	  width: 10px;
	  height: 10px;
	  border-radius: 50%;
	  background-color: #000;
	  margin: 0 10px;
	  animation: pulse 0.8s ease-in-out infinite;
	}

	#dot2 {
	  animation-delay: 0.1s;
	}

	#dot3 {
	  animation-delay: 0.2s;
	}

	@keyframes pulse {
	  0% {
		transform: scale(1);
		opacity: 1;
	  }
	  50% {
		transform: scale(1.5);
		opacity: 0.5;
	  }
	  100% {
		transform: scale(1);
		opacity: 1;
	  }
	}

	/* Set the width and height of the scrollbar */
	.chat_window::-webkit-scrollbar {
	  width: 5px;
	  height: 5px;
	}.chat_window::-moz-scrollbar {
	  width: 5px;
	  height: 5px;
	}
	

	/* Define the track and thumb styles */
	.chat_window::-webkit-scrollbar-track {
	  background: #f1f1f1;
	}.chat_window::-moz-scrollbar-track {
	  background: #f1f1f1;
	}

	.chat_window::-webkit-scrollbar-thumb {
	  background: #888;
	}
	.chat_window::-moz-scrollbar-thumb {
	  background: #888;
	}

	/* Style the scrollbar on hover */
	.chat_window::-webkit-scrollbar-thumb:hover {
	  background: #555;
	}.chat_window::-moz-scrollbar-thumb:hover {
	  background: #555;
	}
	
	/* Style for the chat window */
	#chat_window {
		width: 100%;
		border: 1px solid black;
		overflow-y: scroll;
		height: 100%;
	}
	/* Style for chat messages */
	.message {
		margin-bottom: 10px;
		white-space: break-spaces;
		padding: 10px;
		background-color: #eee;
		margin-top: 0px;    
		word-wrap: break-word;
	}
	/* Style for replied messages */
	.replied-message {
		white-space: break-spaces;
		padding: 10px;
	}

	/* style for input text */
	.textarea_input{
		width: 100%;
		resize: none;
		height: 68px;
		box-sizing: border-box;
	}
	
	/* style for send button */
	.send_button{
		width: 90%;
		height: 68px; 
		vertical-align: top;
		box-sizing: border-box;
	}
	
    table.tb_style{
        table-layout: fixed;
    }
    table.tb_style > tbody > tr > td{
        box-sizing: border-box;
    }
    </style>
    <script>
    // Managing Data in Local Storage with JavaScript [start]
    function write_item(id,data){
        localStorage.setItem(id, data);
    }

    function read_item(id){
        var saved_data = localStorage.getItem(id);
        return saved_data;
    }

    function check_item(id){
        var status = false;
        if(localStorage.getItem(id) !== null) {
            // The key exists in the localStorage
            status = true;
        } else {
            // The key does not exist in the localStorage
            status = false;
        }
        return status;
    }

    function delete_item(id){
        localStorage.removeItem(id);
    }

    function delete_all_item(){
        localStorage.clear();
    }
    // Managing Data in Local Storage with JavaScript [end  ]
        
    // Enhancing User Preferences through Select Option and Local Storage in JavaScript [start]
    function selected_option(element){
      var id = element.id;
      var data = element.value;

      write_item(id,data);
      console.log("Set Option : "+ data);
    }

    function load_selected_option(id){
      var data   = read_item(id);
      var status = check_item(id);
      console.log("Load Option : "+ data);
      if(status == true){
         try {document.getElementById(id).value = data;}catch(err) {console.error(err);}
      }
    }
    // Enhancing User Preferences through Select Option and Local Storage in JavaScript [end  ]
    </script>
  </head>
  <body>
    <table cellpadding="0" cellspacing="0" border="0" style="width: 100%;height: 100%;" class="tb_style">
        <tr style="height:80%;">
            <td style="width: 100%; padding: 8px 8px 0px 8px;">
                <div id="chat_window" class="chat_window"></div>
            </td>
        </tr>
        <tr style="height:10px;"></tr>
        <tr style="height:auto;">
            <td style="width: 100%; padding: 0px 8px;">
                <!-- 20230608 [start] addon select option [temperature] -->
                <table style="width:100%;" cellpadding="0" cellspacing="0" border="0" class="tb_style">
                    <tr>
                        <td style="width:auto;height: 100%;">
                            <input type="button" value="Delete Chat History" onclick="delete_chat_history();">
                            <script>
                                function delete_chat_history(){
                                    let temp_text = "Are you sure to delete the Chat History?";
                                    if (confirm(temp_text) == true) {
                                        delete_item('localstorage_chatHistory');
                                        delete_item('chat_window_html');
                                        location.reload();
                                    }else {
                                        
                                    }
                                }
                            </script>
                        </td>
                        <td style="width:1px;height: 100%;text-align: right;">
                        </td>
                    </tr>
                    <tr style="height:5px;"></tr>
                    <tr>
                        <td style="width:auto;height: 100%;">
                            <label for="chat_gpt_temperature" title="The higher the value, the more creative it becomes.">
                                Select Model:
                            </label>

                            <select name="chat_gpt_model" id="chat_gpt_model" onchange="selected_option(this);">
                                <option value="gpt3_5"      selected>GPT 3.5 [Faster] </option>
                                <option value="gpt3_5_16_k"         >GPT 3.5 16k [Faster with more text]</option>
                                <option value="gpt4"                >GPT 4 [Slower]</option>
                                <option value="gpt4_32_k"           >GPT 4 32k [Slower with more text]</option>
                            </select>
                            <script>load_selected_option("chat_gpt_model");</script>
                        </td>
                        <td style="width:1px;height: 100%;text-align: right;">
                        </td>
                    </tr>
                    <tr>
                        <td style="width:auto;height: 100%;">
                            <label for="chat_gpt_temperature" title="The higher the value, the more creative it becomes.">
                                Select Temperature for Chat GPT:
                            </label>

                            <select name="chat_gpt_temperature" id="chat_gpt_temperature" onchange="selected_option(this);">
                                <option value="0.1">0.1</option>
                                <option value="0.2">0.2</option>
                                <option value="0.3">0.3</option>
                                <option value="0.4">0.4</option>
                                <option value="0.5" selected>0.5</option>
                                <option value="0.6">0.6</option>
                                <option value="0.7">0.7</option>
                                <option value="0.8">0.8</option>
                                <option value="0.9">0.9</option>
                                <option value="1">1.0</option>
                                <option value="10">10</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                                <option value="10000">10000</option>
                                <option value="100000">100000</option>
                            </select>  
                            <script>load_selected_option("chat_gpt_temperature");</script>
                        </td>
                        <td style="width:1px;height: 100%;text-align: right;">
                        </td>
                    </tr>
                    <tr>
                        <td style="width:auto;height: 100%;">
                            <label for="chat_gpt_prompt" title="Select Default Prompt">
                                Select Prompt for Chat GPT:
                            </label>

                            <select name="chat_gpt_prompt" id="chat_gpt_prompt" onchange="selected_option(this);">
                                <option value="" selected>None Prompt</option>
                                <option value="我需要你模拟一个角色。 角色名字是Jarvis. 1. 分析和理解 2. 检测和纠正拼写错误 3. 语法纠错和调整 4. 识别并解析句子结构 5. 提取关键词和概念 6. 分类判断信息的重要性和可靠性 7. 提供问题的答案或解决方案 8. 分析用户情绪和语气 9. 提供在特定领域或主题下的专业意见 10. 生成摘要或概述 11. 生成合适的回复或建议 12. 提供相关的学术文献和研究资料 13. 必要时提供相关链接作为参考，链接必须以http  or https 为开头 14. 实时翻译文本信息 15. 识别和提取文本中的时间和日期 16. 分析信息的情感倾向和态度 17. 提取文本中的实体信息(人物、地点、组织等) 18. 分析大规模文本数据，提供洞察和趋势 19. 生成报告和汇总 20. 生成推荐和建议 21. 提供操作说明和帮助文档的链接 22. 精确的语言生成和回答用户的问题 23. 如果回答用户前，有不了解的疑虑，请提问如果理解了以上内容，请以助手的模式进行沟通。">Jarvis</option>
                                <option value="I need you to simulate a role. The character's name is a Web Developer specializing in JavaScript, CSS, and HTML. Don't use API. I will provide you with my requirements and detailed information. Your task is to use HTML, CSS, and JavaScript to help me create a web page. All of the code must be written in as much detail and completeness as possible. If my requirements seem too simple, you can add more functionality and improve upon them. Your goal is to perfect my requirements to the best of your ability. When writing the code, you must consider the user experience and all aspects of future maintenance, in order to avoid the need for subsequent engineers to clean up after you. Let's start by creating a web page using the provided information:">Web Developer</option>
                                <option value="I will provide INPUT. Help me fix my INPUT grammar issues if any, and do corrections if have any errors. And also an explanation of the grammar logic and provide some example sentences for me to learn more about my mistake if any. My INPUT is :">Fix Grammar</option>
                                <option value="我需要你模拟一个角色。角色名字是Google Map Reviewer Expert. 我会提供你地点名字，详情等信息。然后你会用英语帮我输出成一个长达210字的Google Review。请参考以下Sample的模式来写Google Review，用类似的语法，语调，类型。 Sample Review 1 : This mall is just tooo big has so many gates to enter,This place is on permonade, which is on circle and blue line. This place has so many shopping places and food places to eat . Also got golden village for movies.  Since it too big sometime it's kinda hard to find lot of places for a new person. It was clean and well managed. Sample Review 2 : Shopping mall built around the Fountain of Wealth. Lots of shops and restaurants including Giant grocery store (here you can eat good and cheap freshly prepared food). All of those are underground with windows towards the fountain. According to Guinness Word Records this is the biggest fountain in the world. There is a scheduled access inside the fountain where is said if you make three trips around the centre part of the fountain will bring you wealth. The line for the entrance is quite long at times but it goes pretty fast. With all the luck gathered by circling the fountain, you can replenish your energy at one of the great eateries in the circle shaped mall around the fountain.请你开始帮我用以下提供的信息写Google Review：">Google Map Review Writer</option>
                                <option value="假设你的职业是调查员/调查分析师 (Investigator/Investigation Analyst)。你有有良好的分析思维能力，能够进行复杂的调查和分析，并能够将结果以简单易懂的方式传达给其他人。你也具备良好的沟通能力和写作能力，用清晰、简明的语言传达调查结果。你的每次回答必须最低1000字。你的回答方式必须以,调查员回答:,开头。">调查员/调查分析师</option>
                                <option value="假设你的职业是一名报告专家。你有良好的逻辑思维能力，能够进行复杂的调查和分析前因后果，并能够将调查结果和信息以简单易懂的方式传达给其他人。你也具备优秀的沟通能力和写作能力，用清晰、简明的语言撰写报告。你的每次回答都是以报告的方式回答。你的回答方式必须以,报告专家回答:,开头。每次回答必须最少1000字。">报告专家</option>
                                <option value="你的回答必须最少1000字。假设你的职业是一名优秀的作家。你有良好的逻辑思维能力，能够进行复杂的调查和分析前因后果，热爱写作，有创造力和想象力，具备历史、文学、科学等的知识积累，在文学创作中，能够准确地使用背景和描述，突出文学作品的真实性和可信度。你也具备良好的语言表达能力，具备清晰、生动、精练、富有表现力的语言表达能力，使作品能够准确地传达思想和情感，让读者感受到文学作品的美。你也善于观察和思考，从细节中挖掘出作品的主题和意境，创造出更深刻的文学作品。你的回答方式必须以,优秀的作家回答:,开头。">优秀的作家</option>
                                <option value="
                                角色设定:信息分析师.信息分析师是指从大量的信息数据中，提取有价值的信息并对其加以分析和评估，得出一定结论的专业人员。他们的目的通常是支持一项特定业务或项目的发展，或提供策略性决策支持。信息分析师通常需要使用各种工具和技术来处理和分析数据，例如数据挖掘、数据分析和数据可视化等。他们需要具备专业知识和技能，以能够筛选非常复杂的数据，识别出隐藏的关联性，发现数据中的不一致性和偏差。信息分析师也需要将得到的信息整合，以帮助决策者做出正确的决策。信息分析师可以在各种各样的领域工作，例如金融、物流、市场营销、零售、医疗等。他们通常需要与业务部门和高层领导合作，以制定业务战略、推动创新和优化业务流程。
                                总之，信息分析师是指能够以数据分析的方式快速处理和提取价值信息，从而帮助机构做出更多高质量决策的专业人员。假设你的角色是一名信息分析师， 请你用信息分析师的回答方式回答我。你的回答方式必须以,信息分析师回答:,开头。">信息分析师</option>
                                <option value="
                                角色设定:报告专家。报告专家是指以下步骤来撰写报告，收集和整理相关资料：收集与报告相关的信息和数据，并对其进行整理、分类、筛选和分析，以便在报告中进行准确的陈述和说明。制定报告的大纲和结构：依据报告的目的和受众，制定相应报告的大纲和结构，以便有条理地呈现信息和数据。编写报告正文：根据大纲，逐步撰写报告的正文，内容应当准确、简明、清晰、逻辑严密。补充图表说明：利用图表和表格等方式，便于受众对信息进行理解和比较。审核和修订：对报告进行审核和修订，确保其准确性和完整性。假设你的角色是一名报告专家， 请你用报告的方式回答我。你的回答方式必须以,报告专家回答:,为开头，以，累计字数和要求字数，为结尾。我要求的报告字数是25000字，你先写500字。主题是：">报告专家v2</option>
                                <option value="你是一個專家級ChatGPT提示工程師，在各種主題方面具有專業知識。在我們的互動過程中，你會稱我為 (yourname）。讓我們合作創建最好的ChatGPT響應我提供的提示。我們將進行如下交互： 1.我會告訴你如何幫助我。 2.根據我的要求，您將建議您應該承擔的其他專家角色，除了成為專家級ChatGPT提示工程師之外，以提供最佳響應。然後，您將詢問是否應繼續執行建議的角色，或修改它們以獲得最佳結果。 3.如果我同意，您將採用所有其他專家角色，包括最初的Expert ChatGPT Prompt Engineer角色。 4.如果我不同意，您將詢問應刪除哪些角色，消除這些角色，並保留剩餘的角色，包括專家級ChatGPT Prompt工程師角色，然後再繼續。 5. 您將確認您的活動專家角色，概述每個角色下的技能，並詢問我是否要修改任何角色。 6.如果我同意，您將詢問要添加或刪除哪些角色，我將通知您。重複步驟5，直到我對角色滿意為止。 7如果我不同意，請繼續下一步。 8.你會問：“我怎樣才能幫助[我對步驟1的回答]？ 9.我會給出我的答案。 10.你會問我是否想使用任何參考來源來製作完美的提示。 111.如果我同意，你會問我想使用的來源數量。 12.您將單獨請求每個來源，在您查看完後確認，並要求下一個。繼續，直到您查看了所有源，然後移動到下一步。 13.您將以列表格式請求有關我的原始提示的更多細節，以充分了解我的期望。 14.我會回答你的問題。 15.從這一點開始，您將在所有確認的專家角色下操作，並使用我的原始提示和步驟14中的其他細節創建詳細的ChatGFT提示。提出新的提示並徵求我的反饋。 16.如果我滿意，您將描述每個專家角色的貢獻以及他們將如何協作以產生全面的結果。然後，詢問是否缺少任何輸出或專家。 16.1.如果我同意，我將指出缺少的角色或輸出，您將在重複步驟15之前調整角色。 16.2.如果我不同意，您將作為所有己確認的專家角色執行提供的提示，並生成步驟15中概述的輸出。繼續執行步驟20 ° 17.如果我不滿意，你會問具體問題的提示。 18.我將提供補充資料。 19.按照步驟15中的流程生成新提示，並考慮我在步驟18中的反饋。 20.完成回復後，詢問我是否需要任何更改。 21.如果我同意，請請求所需的更改，參考您之前的回复，進行所需的調整，並生成新的提示。重複步驟15-20，直到我對提示符滿意為止。如果你完全理解你的任務，回答：”我今天能幫你什麼，(your name)”">專家級ChatGPT提示工程師</option>
                                <option value="如果我输入一个中文单词，你就帮我用翻译去英文，并加以解释。如果我输入一段中文句子，你就帮我用正确的英语语法翻译去英语，并加以解释语法结构。:”">中英翻译</option>
                                <option value="如果我输入一个英文单词，你就帮我用翻译去中文，并加以解释。如果我输入一段英文句子，你就帮我用正确的中文语法翻译去中文，并加以解释语法结构。:”">英中翻译</option>
                            </select>  

                            <script>load_selected_option("chat_gpt_prompt");</script>
                        </td>
                        <td style="width:1px;height: 100%;text-align: right;">
                        </td>
                    </tr>
                </table>
                <!-- 20230608 [end  ] addon select option [temperature] -->


                <table style="width:100%;height: 10vh;" cellpadding="0" cellspacing="0" border="0" class="tb_style">
                    <tr>
                        <td style="width:85%;height: 100%;">
                            <textarea id="message-input" class="textarea_input" value="" rows="1" placeholder="Type your message here"></textarea>
                        </td>
                        <td style="width:15%;height: 100%;text-align: right;">
                            <button id="send-button" class="send_button" onclick="send_message();">Send</button>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    
    
    <script>
        
	var currentMessage = "";
	let previousMessage = "";
	let role_system_content = ""; // 20230608
	let chatHistory = [];
	var password = "";
	var KEY = "";
	var apiKey = getCookie("api_key");
	
	const container = document.getElementById("container"); //Get the parent element

	const loading = document.createElement("div"); //Create a new div element
	loading.classList.add("loading"); //Add the "loading" class to it

	//Append the child div elements to the parent div
	loading.appendChild(document.createElement("div")).id = "dot1";
	loading.appendChild(document.createElement("div")).id = "dot2";
	loading.appendChild(document.createElement("div")).id = "dot3";

	// Create a TextEncoder instance
	const encoder = new TextEncoder();

	// Create a TextDecoder instance
	const decoder = new TextDecoder();
	
	// Encrypt function
	function encrypt(message) {
		let ciphertext = '';
		const encodedMessage = encoder.encode(message);
		for (let i = 0; i < encodedMessage.length; i++) {
			let charCode = encodedMessage[i];
			let keyChar = KEY.charCodeAt(i % KEY.length);
			let encryptedChar = charCode ^ keyChar;
			let numValue = encryptedChar.toString().padStart(3, '0');
			ciphertext += numValue;
		}

		return ciphertext;
	}

	// Decrypt function
	function decrypt(ciphertext) {
		let decodedMessage = '';
		for (let i = 0; i < ciphertext.length; i += 3) {
			let numStr = ciphertext.slice(i, i + 3);
			let encryptedChar = parseInt(numStr);
			let keyChar = KEY.charCodeAt((i / 3) % KEY.length);
			let decryptedChar = encryptedChar ^ keyChar;
			decodedMessage += String.fromCharCode(decryptedChar);
		}
		return decoder.decode(new Uint8Array(encoder.encode(decodedMessage)));
	}
			
	function getCookie(name) {
		const cookies = document.cookie.split(';');
		for (let i = 0; i < cookies.length; i++) {
			const cookie = cookies[i].trim();
			if (cookie.startsWith(`${name}=`)) {
				const value = cookie.substring(name.length + 1);
				return decodeURIComponent(value);
			}
		}
		return '';
	}

	  function chatgpt(content){
			// default 
			currentMessage = content;
			// Set up the API endpoint URL
			var apiUrl = "";
			var model = "";
			var selected_model = "turbo"; // davinci, turbo

			if(selected_model == "davinci"){
				apiUrl = 'https://api.openai.com/v1/completions';
				model = 'text-davinci-003';
			}else if(selected_model == "turbo"){
				apiUrl = 'https://api.openai.com/v1/chat/completions';
				model = 'gpt-3.5-turbo';
			}

			// Set up the request data
			const prompt = 'how old are you';
			const maxTokens = 4096;
			var temperature = 0.5; // Default 0.5 
          
            // 20230708 [start] addon chat_gpt_model
            var chat_gpt_model = document.getElementById("chat_gpt_model");
            if(chat_gpt_model){
                if(chat_gpt_model.value == "gpt3_5"){
                    apiUrl = 'https://api.openai.com/v1/chat/completions';
                    model = 'gpt-3.5-turbo';
                }else if(chat_gpt_model.value == "gpt4"){
                    apiUrl = 'https://api.openai.com/v1/chat/completions';
                    model = 'gpt-4';
                }else if(chat_gpt_model.value == "gpt3_5_16_k"){
                    apiUrl = 'https://api.openai.com/v1/chat/completions';
                    model = 'gpt-3.5-turbo-16k';
                }else if(chat_gpt_model.value == "gpt4_32_k"){
                    apiUrl = 'https://api.openai.com/v1/chat/completions';
                    model = 'gpt-4-32k';
                }
                console.log("Chat GPT Model : " + chat_gpt_model.value);
            }
            // 20230708 [start] addon chat_gpt_model
          
            // 20230608 [start] addon chat_gpt_temperature
            var chat_gpt_temperature = document.getElementById("chat_gpt_temperature");
            if(chat_gpt_temperature){
                temperature = chat_gpt_temperature.value; // Update
            }
            console.log("Chat GPT temperature : " + temperature);
            // 20230608 [start] addon chat_gpt_temperature
          
            // 20230608 [start] addon chat_gpt_prompt
            var chat_gpt_prompt = document.getElementById("chat_gpt_prompt");
            if(chat_gpt_prompt){
                role_system_content = chat_gpt_prompt.value; // Update
            }
            console.log("Chat GPT Prompt : " + role_system_content);
            // 20230608 [end  ] addon chat_gpt_prompt
            
			var requestData = {};
            
            // 20230921 [start] addon chatHistory to localstorage
            var localstorage_chatHistory_check = check_item("localstorage_chatHistory");
            var localstorage_chatHistory = read_item("localstorage_chatHistory");
            
            if(localstorage_chatHistory_check == true){
                if (localstorage_chatHistory.length === 0) {
                    //console.log("localstorage_chatHistory is empty");
                } else {
                    //console.log("localstorage_chatHistory is not empty");
                    //console.log(typeof localstorage_chatHistory);
                    //console.log(localstorage_chatHistory);
                    
                    var localstorage_chatHistory_json_parse = JSON.parse(localstorage_chatHistory);
                    chatHistory = localstorage_chatHistory_json_parse;
                }
            }
            // 20230921 [end  ] addon chatHistory to localstorage
				
			if(selected_model == "davinci"){
				requestData = {
					"model": model,
					"prompt": prompt,
					"temperature": parseFloat(temperature),
					"max_tokens": parseInt(maxTokens)
				};
			}else if(selected_model == "turbo"){
				requestData = {
					  "model": model,
					  "messages":	[
										{
										  "role": "system",
										  "content": role_system_content
										},
										...chatHistory,
										{
										  "role": "user",
										  "content": currentMessage
										}
									]
					};
                    console.log(requestData.messages.find(message => message.role === 'system').content);
					
                
                    chatHistory.push({
						"role": "user",
						"content": currentMessage
					});
                    
                    var chatHistory_json_stringify = JSON.stringify(chatHistory);
                    write_item("localstorage_chatHistory",chatHistory_json_stringify);
                        
                    for (let history_message of chatHistory) {
                      console.log(history_message);
                    }
                
					previousMessage = currentMessage;
			}

			// Set up the AJAX request
			const xhr = new XMLHttpRequest();
			xhr.open('POST', apiUrl, true);
			xhr.setRequestHeader('Content-Type', 'application/json');
			xhr.setRequestHeader('Authorization', `Bearer ${apiKey}`);

			xhr.onreadystatechange = function() {
			  if (xhr.readyState === 4) {
				if (xhr.status === 200) {
				  // Process the response
				  const response = JSON.parse(xhr.responseText);
				  console.log(response); // log the entire response object
				  if (response.choices && response.choices.length > 0 && response.choices[0].message && response.choices[0].message.content) {
					const text = response.choices[0].message.content.trim();
					
					if (text) {
						
						try{
				            var loadingDiv = document.querySelector('.loading');
                            loadingDiv.parentNode.removeChild(loadingDiv);// remove loading
                        }catch{}
						
						var repliedMessageElement = document.createElement("p");
						repliedMessageElement.classList.add("replied-message");
						repliedMessageElement.textContent = "Chat GPT : \n";
						chatWindow.appendChild(repliedMessageElement);
								
						// var texts = text.split(/(```(?:[^`]|(?<=`)`)+```)|(`(?:[^`]|(?<=`)`)+`)/g);
                        // var texts = text.split(/```([^`]*)```/g);// /```(.*?)```/gs
                        var texts = text.split(/``(.*?)```/gs);// /```(.*?)```/gs
						texts.forEach(function (txt) {
							if (txt) {
                                var replace_txt =	"";
                                let codeRegex = /[\{\}\[\]\/?.,;:|\)*~`!^\-_+<>@\#$%&\\\=\(\'\"]/g;
                                // let containsCode = codeRegex.test(txt);

								var element = document.createElement(txt.startsWith("`") ? "code" : "p"); // check for `
								element.classList.add("replied-message");
								
                                replace_txt =	txt.replace(/^`javascript\b/, "")
                                                    .replace(/^`html\b/, "")
													.replace(/^`php\b/, "")
													.replace(/^`css\b/, "")
													.replace(/^`python\b/, "")
													.replace(/^`html\b/, "")
													.replace(/^`ruby\b/, "")
													.replace(/^`java\b/, "")
													.replace(/^`typescript\b/, "")
													.replace(/^`csharp\b/, "")
													.replace(/^`c\b/, "")
													.replace(/^`cpp\b/, "")
													.replace(/^`bash\b/, "")
													.replace(/^`powershell\b/, "")
													.replace(/^`kotlin\b/, "")
													.replace(/^`go\b/, "")
													.replace(/^`swift\b/, "")
													.replace(/^`scala\b/, "")
													.replace(/^`/, "")
								element.textContent = replace_txt;
								
                                if(replace_txt != ""){
                                    // record CHAT GPT Response Message 
                                    chatHistory.push({
                                        "role": "assistant",
                                        "content": txt
                                    });
                                }

								if (txt.startsWith("`") || txt.endsWith("`")) {
									element.classList.add("language-code");
									var copyButton = document.createElement("button");
									copyButton.textContent = "Copy";
									copyButton.classList.add("code_copy_button");
                                    chatWindow.appendChild(copyButton);
                                    
                                    
                                    var div_element = document.createElement("div");
									div_element.classList.add("div_code");
								    div_element.appendChild(element);
                                    element = div_element;
								}
                                
								chatWindow.appendChild(element);
                                
                                if (txt.startsWith("`") || txt.endsWith("`")) {setTimeout(function() {reset_code_copy_buttun_status();}, 100);}
								chatWindow.scrollTop = chatWindow.scrollHeight; // scroll down to the bottom
								lastReply = element;
                                detect_and_generate_clickable_link();
                                
                                var chatWindow_innerHTML = chatWindow.innerHTML;
                                write_item("chat_window_html", chatWindow_innerHTML);
							}
						});
					}
				  } else {
					console.error("API response is missing or incomplete");
				  }
				} else {
				  console.error('API request failed with status ${xhr.status}');
				  chatgpt(currentMessage); // send message again
				}
			  }
			};
			// Send the request
			xhr.send(JSON.stringify(requestData));

	  
	  }
        function detect_and_generate_clickable_link() { // check valid link and update it to clickable link
            console.log("start");
            var repliedMessages = document.getElementsByClassName("replied-message");
            for (var i = repliedMessages.length - 1; i < repliedMessages.length; i++) { // only update last row; save cost
            var message = repliedMessages[i].innerHTML;
            var regex = /(http:\/\/|https:\/\/)[^\s]+/g;
            var replacedMessage = message.replace(regex, "<a href='$&' target='_blank'>$&</a>");
            repliedMessages[i].innerHTML = replacedMessage;
            }
        }
        
		const chatWindow = document.getElementById("chat_window");
		const messageInput = document.getElementById("message-input");
		const sendButton = document.getElementById("send-button");

		// Select the input element
		const inputBox = document.querySelector("#message-input");
        
        // 20230921 [start] addon chatHistory to localstorage
        var localstorage_chat_window_html_check = check_item("chat_window_html");
        var localstorage_chat_window_html = read_item("chat_window_html");
        if(localstorage_chat_window_html_check == true){
            if (localstorage_chat_window_html == null) {
                //console.log("localstorage_chat_window_html is empty");
            } else {
                //console.log("localstorage_chat_window_html is not empty");
                //console.log(typeof localstorage_chat_window_html);
                //console.log(localstorage_chat_window_html);
                
                if(chatWindow.innerHTML == ""){
                    //console.log("chatWindow is empty");
                    //console.log(localstorage_chat_window_html);
                    chatWindow.innerHTML = localstorage_chat_window_html;
                }
            }
        }
        // 20230921 [start] addon chatHistory to localstorage

		// Add event listener to detect focus
		inputBox.addEventListener("focus", function(event) {
			// Add event listener to detect enter key press
			inputBox.addEventListener("keypress", function(event) {
				if (event.key === "Enter" && event.shiftKey) {
					// Add your code here
					console.log("Shift + Enter detected");
				}else if (event.key === "Enter") {
					// Call your function here
					sendButton.click();
				}
			});
		});
		
      // Keep track of the last message and reply
      let lastMessage = null;
      let lastReply = null;

      // Add event listener for the send button
      function send_message(){
        var message = messageInput.value;
        if (message) {
          // Create a new chat message element and add it to the chat window
          var messageElement = document.createElement("pre");
          messageElement.classList.add("message");
          messageElement.textContent = "You : \n"+message;
          chatWindow.appendChild(messageElement);
		  chatWindow.appendChild(loading); //Add the entire "loading" div to the chatWindow element
		  chatWindow.scrollTop = chatWindow.scrollHeight; // scroll down to the bottom

		  lastMessage = chatgpt(message);
          // Clear the message input
          messageInput.value = "";
        }
	  }
    function convertEntitiesToSymbols(text) {
      var textarea = document.createElement("textarea");
      textarea.innerHTML = text;
      return textarea.value;
    }
    function reset_code_copy_buttun_status(){
        // Get all buttons with the class name "code_copy_button"
        const code_copy_button = document.getElementsByClassName("code_copy_button");
        const code_area = document.getElementsByClassName("language-code");
        
        // Iterate through the buttons and add event listeners
        for (let i = 0; i < code_copy_button.length; i++) {
          code_copy_button[i].addEventListener("click", function() {
            let data_to_copy = code_area[i].innerHTML;
            console.log("copying : "+data_to_copy);
            data_to_copy = convertEntitiesToSymbols(data_to_copy);
            console.log("convertEntitiesToSymbols : "+data_to_copy);

            let textArea = document.createElement("textarea");
            textArea.value = data_to_copy;
            textArea.style.height = "1px";   
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();

          });
        }
    }
	document.addEventListener("DOMContentLoaded", function() {
		// Code to be executed when the DOM is ready
		document.getElementById('message-input').value = '';
		
		
		// Check if 'password' cookie exists
		if(!document.cookie.split(';').some((item) => item.trim().startsWith('password='))) {
			// Alert box if 'password' cookie not found
			// Prompt box to get Password
			password = prompt("Please enter your Password:");
			// Create a Date object for 1 year in the future
			var expiryDate = new Date();
			expiryDate.setFullYear(expiryDate.getFullYear() + 1);

			// Create the cookie string with user ID and expiry date
			var cookieString = "password=" + password + ";expires=" + expiryDate.toUTCString();

			// Set the cookie in the browser
			document.cookie = cookieString;
		
		}
		
		// Check if 'password' cookie exists
		if(!getCookie("api_key")) {
			// Key used to encrypt and decrypt the message
			KEY = getCookie("password");
			console.log("KEY password : " + KEY);


			var decrypt_data = decrypt("069089026011110108087086103119109096003117125098077014076090123007090098005112091080082112124065100122120114089104090010091112066074114005097066004085092");
			console.log("decrypt : " + decrypt_data);
			
			// Create a Date object for 1 year in the future
			var expiryDate = new Date();
			expiryDate.setFullYear(expiryDate.getFullYear() + 1);
			
			// Create the cookie string with user ID and expiry date
			var cookieString = "api_key=" + decrypt_data + ";expires=" + expiryDate.toUTCString();

			// Set the cookie in the browser
			document.cookie = cookieString;
			
			apiKey = getCookie("api_key");
			console.log("api_key : " + apiKey);
		
		}
		
		
		// Textarea will be focus after page loaded
		document.getElementById('message-input').focus();

		
	});
        
    </script>
  </body>
</html>
