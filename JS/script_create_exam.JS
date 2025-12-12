document.addEventListener("DOMContentLoaded", function () {
  const questionContainer = document.getElementById("questionsContainer");

  Sortable.create(questionContainer, {
    animation: 150,
    handle: ".drag-handle",
    onEnd: function () {
      updateQuestionOrder();
    },
  });

  document.querySelectorAll(".options-list").forEach((list) => {
    Sortable.create(list, {
      animation: 150,
      handle: ".drag-handle",
      onEnd: function () {
        updateOptionOrder(list);
      },
    });
  });

  function updateQuestionOrder() {
    const rows = document.querySelectorAll("#questionsContainer .question-row");
    rows.forEach((row, index) => {
      const qid = row.getAttribute("data-qid");
      let input = row.querySelector(".sort-order-input");
      if (!input) {
        input = document.createElement("input");
        input.type = "hidden";
        input.name = `sort_order[${qid}]`;
        input.className = "sort-order-input";
        row.appendChild(input);
      }
      input.value = index + 1;
    });
  }

  function updateOptionOrder(list) {
    const items = list.querySelectorAll(".option-item");
    const qid = list.getAttribute("data-qid");
    items.forEach((item, index) => {
      const optid = item.getAttribute("data-optid");
      let input = item.querySelector(".opt-sort-input");
      if (!input) {
        input = document.createElement("input");
        input.type = "hidden";
        input.name = `option_sort[${qid}][${optid}]`;
        input.className = "opt-sort-input";
        item.appendChild(input);
      }
      input.value = index + 1;
    });
  }

  updateQuestionOrder();
  document
    .querySelectorAll(".options-list")
    .forEach((list) => updateOptionOrder(list));
});
