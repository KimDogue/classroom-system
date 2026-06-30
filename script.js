const searchInput =
document.getElementById("searchInput");

searchInput.addEventListener("keyup", () => {

    let filter =
    searchInput.value.toLowerCase();

    let cards =
    document.querySelectorAll(".room-card");

    cards.forEach(card => {

        let text =
        card.innerText.toLowerCase();

        if(text.includes(filter)){
            card.style.display = "";
        }else{
            card.style.display = "none";
        }

    });

});