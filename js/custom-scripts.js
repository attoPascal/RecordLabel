$(function() {
	$("#otherDuration").hide();
	$("#otherPitch").hide();

	$("#durationSelect").change(function() {
		if (this.value == "other") {
			$("#otherDuration").show();
		} else {
			$("#otherDuration").hide();
		}
	})

	$("#pitchSelect").change(function() {
		if (this.value == "other") {
			$("#otherPitch").show();
		} else {
			$("#otherPitch").hide();
		}
	});

	$("input[name=symbol]").change(function() {
		if (this.value == "pause") {
			$("#pitchInputs").hide();
		} else {
			$("#pitchInputs").show();
		}
	});

	$("#levelUpModal").modal("show");
});