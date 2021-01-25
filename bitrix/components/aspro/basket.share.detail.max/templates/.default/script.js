if(typeof window.JCBasketShareDetail === 'undefined'){
	window.JCBasketShareDetail = function(arParams, arResult){
		this.timerCopy = false;

		this.params = {};
		this.result = {};

		this.$block = false;
		this.$onoffHead = false;
		this.$btnAdd2Basket = false;
		this.$btnReplaceBasket = false;
		this.$form = false;
		this.$error = false;

		if(typeof arResult === 'object'){
			this.result = arResult;
		}

		if(typeof arParams === 'object'){
			this.params = arParams;

			this.init();
		}
	};

	window.JCBasketShareDetail.prototype = {
		init: function(){
			this.$block = $('#basket-share-detail');

			if(this.$block.length){
				this.$form = this.$block.find('form');
				this.$onoffHead = this.$block.find('.basket-share-detail__head__onoff');
				this.$btnAdd2Basket = this.$block.find('.basket-share-detail__foot__btn--add2basket');
				this.$btnReplaceBasket = this.$block.find('.basket-share-detail__foot__btn--replacebasket');
				this.$error = this.$block.find('.basket-share-detail__error__text');
			}

			this.updateActionButtons();

			this.bindEvents();
		},

		sendForm: function(bJson, callback){
			var url = this.result.AJAX_URL;
			if(url){
				var data = this.$form.serialize();
				var that = this;

				$.ajax({
					url: url,
					type: 'POST',
					data: data,
					dataType: bJson ? 'json' : 'text',
					beforeSend: function(){
						that.$block.addClass('sending');
					},
					success: function(response){
						if(!bJson){
							that.$block.wrap('<div></div>');
							var $wrap = that.$block.parent();
							$wrap.html(response);
							that.$block = $wrap.find('.basket-share-detail');
							$wrap.find('>div').unwrap('<div></div>');

							if(typeof setStatusButton === 'function'){
								setStatusButton();
							}
						}

						if(typeof callback === 'function'){
							callback(response);
						}
					},
					error: function(xhr, ajaxOptions, thrownError){
						that.$block.addClass('basket-share-detail--haserror');
						that.$error.text(BX.message.CD_T_ERROR_REQUEST + ' ' + thrownError + ' (' + xhr.status + ')');
					},
					complete: function(){
						that.$block.removeClass('sending');
						that.$block.find('.loadings').removeClass('loadings');
					}
				});
			}
		},

		updateActionButtons: function(){
			if(this.$form.length){
				var cntCheckedItems = this.$form.find('.basket-share-detail__item__check input[type=checkbox]:checked').length;

				if(this.$btnAdd2Basket.length){
					this.$btnAdd2Basket.prop('disabled', cntCheckedItems == 0);
				}

				if(this.$btnReplaceBasket.length){
					this.$btnReplaceBasket.prop('disabled', cntCheckedItems == 0);
				}
			}
		},

		bindEvents: function(){
			var that = this;

			if(this.$block.length){
				if(this.$form.length){
					this.$form.submit(function(e){
						e.preventDefault();
					});

					this.$form.find('.basket-share-detail__item__check input[type=checkbox]').change(function(){
						that.updateActionButtons();
					});

					if(this.$onoffHead.length){
						this.$onoffHead.click(function(e){
							e.preventDefault();

							if(
								!that.$block.hasClass('sending') &&
								!$(this).hasClass('loadings')
							){
								$(this).addClass('loadings');
								var bChecked = that.$onoffHead.find('input[type=checkbox]').prop('checked');
								that.$form.find('input[name=ORIGINAL]').val(bChecked ? 'Y' : 'N');
								that.sendForm(false);
							}
						});
					}

					if(this.$btnAdd2Basket.length){
						this.$btnAdd2Basket.click(function(e){
							e.preventDefault();

							if(
								!that.$block.hasClass('sending') &&
								!$(this).hasClass('loadings')
							){
								$(this).addClass('loadings');
								that.$form.append('<input type="hidden" name="ACTION" value="ADD2BASKET" />');
								that.sendForm(true, function(result){
									if(
										!result.ERRORS.length &&
										result.BASKET_PAGE_URL
									){
										location.href = result.BASKET_PAGE_URL;
									}
								});
							}
						});
					}

					if(this.$btnReplaceBasket.length){
						this.$btnReplaceBasket.click(function(e){
							e.preventDefault();

							if(
								!that.$block.hasClass('sending') &&
								!$(this).hasClass('loadings')
							){
								$(this).addClass('loadings');
								that.$form.append('<input type="hidden" name="ACTION" value="REPLACEBASKET" />');
								that.sendForm(true, function(result){
									if(
										!result.ERRORS.length &&
										result.BASKET_PAGE_URL
									){
										location.href = result.BASKET_PAGE_URL;
									}
								});
							}
						});
					}
				}
			}
		},

		onResizeHandler: function(){
			if(typeof CheckPopupTop === 'function'){
				CheckPopupTop();
			}
		},
	};
}
