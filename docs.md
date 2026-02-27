products:{
    user_id

    type: physical
    name, 
    category
    short_description
    description 
    product_price,
    bargaining_price,

    #Publishing
    publish_type: draft/immediatly
    publish_at: date & time
}

product_media_items{
    product_id:-
    cover: image
    
    is_slider: true/false
    media_items: [
        {
            'media_type': upload_video/yt_video/image
            'source_url': 
        }
    ]
}

product_inventory{
    product_id:-
    is_discount_offer: inactive/lifetime/limited
    is_discount_type: inactive/fixed/percentage
    discount_value: ,
    discount_start_at: 
    discount_end_at: 
    available_qty: 
    stock_alert:
    weight:
    shipping_profile:
    color: 
    size: 
}

product_discoverability{
    product_id
    tags
    slug
    meta_title
    meta_description
    seo_tags
}
product_downloadable{
    product_id
    access_type:
    drive_link
    access_instruction:
}
product_subscriptions{
    product_id
    email
    number
    username
    password
    is_authenticated
}