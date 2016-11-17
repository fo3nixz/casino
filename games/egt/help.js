/**
 * ...
 * @author Georgi Dimov gvdimvo@yahoo.com
 */

function getParameterByName(name) 
{
	name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(decodeURIComponent(location.search));

	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
function start()
{
	var gameVersion = getParameterByName("gameVersion");
	if(gameVersion != "") {
		var versionDiv = document.createElement("div");
		versionDiv.innerHTML = gameVersion;
		document.body.appendChild(versionDiv);
	}
	
	var qBets = document.getElementById("qBets");
	if(qBets)
		qBets.innerHTML = getParameterByName("qBets");
		
	var rtpValue = getParameterByName("rtp");
	if(rtpValue != "")
		document.getElementById("rtp").innerHTML = rtpValue + "%";
	else
	{
		var rtpLink = document.getElementById("rtp_link");
		rtpLink.parentNode.removeChild(rtpLink);
		document.body.removeChild(document.getElementById("return_to_player"));
		document.body.removeChild(document.getElementById("rtp_paragraph"));
	}
		
	var currencyType = getParameterByName("currencyType");
	var language = document.getElementsByTagName("html")[0].getAttribute("lang");
	
	var selectImage = document.getElementById("SelectImage");
	var startImage = document.getElementById("StartImage");
	var stopImage = document.getElementById("StopImage");
	var collectImage = document.getElementById("CollectImage");
	var denominationImage = document.getElementById("DenomImage");
	var gambleButton = document.getElementById("GambleButton");
	gambleButton.src = "../../../../../helpImages/"+language+"/gambleButton.png";
	if(currencyType == "EGT")
	{
		selectImage.src = "../../../../../helpImages/"+language+"/selectButtonEGT.png";
		startImage.src = "../../../../../helpImages/"+language+"/startButtonEGT.png";
		stopImage.src = "../../../../../helpImages/"+language+"/stopButtonEGT.png";
		collectImage.src = "../../../../../helpImages/"+language+"/collectButtonEGT.png";
		denominationImage.src = "../../../../../helpImages/"+language+"/denominationButtonEGT.png";
	}
	else
	{
		selectImage.src = "../../../../../helpImages/"+language+"/selectButton.png";
		startImage.src = "../../../../../helpImages/"+language+"/startButton.png";
		stopImage.src = "../../../../../helpImages/"+language+"/stopButton.png";
		collectImage.src = "../../../../../helpImages/"+language+"/collectButton.png";
		denominationImage.src = "../../../../../helpImages/"+language+"/denominationButton.png";
	}
	
	var shouldHideStopAll = JSON.parse(getParameterByName("shouldHideStopAll"));
	var hasAutoplayLimit = JSON.parse(getParameterByName("hasAutoplayLimit"));
	var hasTotalsInfo = JSON.parse(getParameterByName("hasTotalsInfo"));
	
	if(shouldHideStopAll)
	{
		var stopAllRow = document.getElementById("stopAllRow");
		stopAllRow.parentNode.removeChild(stopAllRow);
	}
	
	if(!hasAutoplayLimit)
	{
		if(!hasTotalsInfo)
		{
			var responsibleGaming = document.getElementById("responsibleGaming");
			responsibleGaming.parentNode.removeChild(responsibleGaming);
			var responsibleGamingLink = document.getElementById("responsibleGamingLink");
			responsibleGamingLink.parentNode.removeChild(responsibleGamingLink.nextSibling);
			responsibleGamingLink.parentNode.removeChild(responsibleGamingLink);
		}
		else
		{
			var autoplayLimit = document.getElementById("autoplayLimit");
			autoplayLimit.parentNode.removeChild(autoplayLimit);
		}
		
		var autoplayLimitLink = document.getElementById("autoplayLimitLink");
		autoplayLimitLink.parentNode.removeChild(autoplayLimitLink);
	}
	else if(!hasTotalsInfo)
	{
		var totalsInfo = document.getElementById("totalsInfo");
		totalsInfo.parentNode.removeChild(totalsInfo);
	}
}
