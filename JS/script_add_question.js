document.getElementById("addOptionBtn").onclick = function () {
  const container = document.getElementById("optionsContainer");
  const index = container.children.length;
  const div = document.createElement("div");
  div.className = "input-group mb-2";
  div.innerHTML = `
        <div class="input-group-text">
            <input type="checkbox" name="correct_option[]" value="${index}">
        </div>
        <input type="text" name="options[]" class="form-control" placeholder="Option ${
          index + 1
        }" required>
        <button type="button" class="btn btn-danger removeOption">Remove</button>
    `;
  container.appendChild(div);
  updateRemoveButtons();
};

function updateRemoveButtons() {
  const buttons = document.querySelectorAll(".removeOption");
  buttons.forEach((btn) => {
    btn.onclick = function () {
      const options = document.querySelectorAll(
        "#optionsContainer .input-group"
      );
      if (options.length > 2) {
        this.parentElement.remove();
        reorderCheckboxes();
      } else {
        alert("At least two options are required");
      }
    };
  });
}

function reorderCheckboxes() {
  const checkboxes = document.querySelectorAll(
    "#optionsContainer input[type='checkbox']"
  );
  const inputs = document.querySelectorAll(
    "#optionsContainer input[type='text']"
  );
  checkboxes.forEach((chk, i) => {
    chk.value = i;
    inputs[i].placeholder = "Option " + (i + 1);
  });
}
//updateRemoveButtons();
