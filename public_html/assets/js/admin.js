$(document).ready(function(){
	$("span#delete-guide").click(function(){
		if (confirm("Are you sure you want to delete this guide?")){
			var guideId = $("input[name='guideid']").val();
			window.location.replace("/admin/delete/" + guideId);
		}
	});
});