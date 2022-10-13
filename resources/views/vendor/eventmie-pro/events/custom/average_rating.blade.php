<div class="row">
    <div class="col-xs-6">
        <h3>{{ count($extra['reviews']) }} @lang('eventmie-pro::em.review')</h3>
        <p>
            <span class="fa fa-star {{$extra['average_rating'] >= 1 ? 'star-active' : 'star-inactive' }} mx-1"></span> 
            <span class="fa fa-star {{$extra['average_rating'] >= 2 ? 'star-active' : 'star-inactive' }} mx-1"></span> 
            <span class="fa fa-star {{$extra['average_rating'] >= 3 ? 'star-active' : 'star-inactive' }} mx-1"></span> 
            <span class="fa fa-star {{$extra['average_rating'] >= 4 ? 'star-active' : 'star-inactive' }} mx-1"></span> 
            <span class="fa fa-star {{$extra['average_rating'] >= 5 ? 'star-active' : 'star-inactive' }} mx-1"></span> 
            &nbsp;&nbsp;
            {{ $extra['average_rating'] }} @lang('eventmie-pro::em.out_of_5')
        </p>
    </div>
    
    @if($extra['take_reviews'])
        <div class="col-xs-6 pull-right text-right">
            <button type="button" onclick="document.getElementById('review_modal').style.display = 'block';" class="lgx-btn lgx-btn-red lgx-btn-sm" >
                <i class="fas fa-star"></i> @lang('eventmie-pro::em.add_review')
            </button>
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md-12">
        @if($extra['reviews']->isNotEmpty())
        <br>
        @foreach ($extra['reviews'] as $key => $item)
        <div class="media">
            <div class="media-body">
                <h4 class="media-heading">{{ $item->user['name'] }}</h4>
                <p>
                    <span class="fa fa-star {{ $item->rating >= 1 ? 'star-active' : 'star-inactive' }}"></span> 
                    <span class="fa fa-star {{ $item->rating >= 2 ? 'star-active' : 'star-inactive' }}"></span> 
                    <span class="fa fa-star {{ $item->rating >= 3 ? 'star-active' : 'star-inactive' }}"></span> 
                    <span class="fa fa-star {{ $item->rating >= 4 ? 'star-active' : 'star-inactive' }}"></span> 
                    <span class="fa fa-star {{ $item->rating >= 5 ? 'star-active' : 'star-inactive' }}"></span>
                    &nbsp;&nbsp;
                    {{ $item->rating }} @lang('eventmie-pro::em.out_of_5')
                </p>
                <p>{{$item->review}}</p>
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {{ $extra['reviews']->links() }}
    </div>
</div>


<div class="modal modal-mask"  id="review_modal" >
    <div class="modal-dialog modal-container modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" onclick="document.getElementById('review_modal').style.display = 'none';" class="close"><span aria-hidden="true">&times;</span></button>

                <div class="text-center">
                    <h3 class="title mb-4">@lang('eventmie-pro::em.add_review')</h3>
                </div>
            </div>

            <form method="POST" action="{{route('reviews.store')}}">
                @csrf
                <input type="hidden" value="{{$event->id}}" name="event_id"/>
                <input type="hidden" value="{{ Auth::id()}}" name="user_id"/>

                <div class="media">
                    <div class="media-body">
                        <h4 class="media-heading">@lang('eventmie-pro::em.rating')</h4>
                        <div class="star-rating">
                            <input id="star-5" type="radio" name="rating" value="5" {{ !empty($extra['user_reviews']) ? ($extra['user_reviews']['rating'] == 5.0 ? 'checked' : '') : '' }} />
                            <label for="star-5" title="5 stars">
                            <i class="active fa fa-star" aria-hidden="true"></i>
                            </label>
        
                            <input id="star-4" type="radio" name="rating" value="4" {{ !empty($extra['user_reviews']) ? ($extra['user_reviews']['rating'] == 4.0 ? 'checked' : '') : '' }}/>
                            <label for="star-4" title="4 stars">
                            <i class="active fa fa-star" aria-hidden="true"></i>
                            </label>
        
                            <input id="star-3" type="radio" name="rating" value="3" {{ !empty($extra['user_reviews']) ?  ($extra['user_reviews']['rating'] == 3.0 ? 'checked' : '') : '' }}/>
                            <label for="star-3" title="3 stars">
                            <i class="active fa fa-star" aria-hidden="true"></i>
                            </label>
        
                            <input id="star-2" type="radio" name="rating" value="2" {{ !empty($extra['user_reviews']) ? ($extra['user_reviews']['rating'] == 2.0 ? 'checked' : '') : '' }} />
                            <label for="star-2" title="2 stars">
                            <i class="active fa fa-star" aria-hidden="true"></i>
                            </label>
                            <input id="star-1" type="radio" name="rating" value="1" {{ !empty($extra['user_reviews']) ? ($extra['user_reviews']['rating'] == 1.0 ? 'checked' : '') : '' }} />
                            <label for="star-1" title="1 star">
                            <i class="active fa fa-star" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="review">@lang('eventmie-pro::em.review')</label>
                    <input type="textbox" class="form-control" id="review" name="review" onchange="review(event)" value="{{ !empty($extra['user_reviews']) ? $extra['user_reviews']->review : '' }}" >
                </div>
            
                <button type="submit" class="lgx-btn lgx-btn-success btn-block">@lang('eventmie-pro::em.submit')</button>
            </form>
        </div>
    </div>
</div>