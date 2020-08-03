

const cardBillboard = (id, image, movie, theater, start_date, end_date) => {
  return `<div class="Card--billboard">
      <span class="Card--id"># ${id}</span>
      <img
        class="Card--image"
        src="${image}"
        alt="${name}"
      />
      <h1 class="Card--movie">${name}</h1>
      <h4 class="Card--theater">${theater}</h4>
      <span class="Card--details">
        ${start_date} to ${end_date}
      </span>
    </div>`;
};

const moviebillboard = async (url_load_billboard, currentMonth, currentYear) => {
  await jQuery.ajax({
    type: "POST",
    url: url_load_billboard,
    data: {month: currentMonth, year: currentYear},
    dataType: 'json',
    success: function(data) {
      var obj    = JSON.parse(JSON.stringify(data));
      console.log(obj);
      if(obj.exito == 1) {
        let html = '';
        for(var index = 0; index < obj.moviebillboard.length; index++) {
          const element = obj.moviebillboard[index];
          html += cardBillboard(element.id, element.image, element.movie, element.theater, element.start_date, element.end_date);
        }

        jQuery('#moviebillboard').html(html);
      } else {
        const html = '<div class="without-billborads"><br /><br /><br /><br /><br /><br />Without billborads<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></div>';
        jQuery('#moviebillboard').html(html);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      toastr.error('An error was generated please consult the administrator!', 'ERROR', {closeButton: true, 'progressBar': true, preventDuplicates: true, positionClass: 'toast-bottom-right'});
    }
  });
};
