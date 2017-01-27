//
//  CategoryPTableViewCell_iPhone.h
//  Borhan
//
//  Created by Pavel on 04.03.12.
//  Copyright (c) 2012 Borhan. All rights reserved.
//

#import <UIKit/UIKit.h>

@class CategoryViewController_iPhone;

@interface CategoryPTableViewCell_iPhone : UITableViewCell {
    
    IBOutlet UIView *cell1View;
    IBOutlet BorhanThumbView *cell1Image;
    IBOutlet UILabel *cell1Label1;
    IBOutlet UILabel *cell1Label2;
    
    IBOutlet UIView *cell2View;
    IBOutlet BorhanThumbView *cell2Image;
    IBOutlet UILabel *cell2Label1;
    IBOutlet UILabel *cell2Label2;
    
    int index;
    
    CategoryViewController_iPhone *parentController;

}

- (void)updateCell1:(BorhanMediaEntry *)mediaEntry;
- (void)updateCell2:(BorhanMediaEntry *)mediaEntry;

- (IBAction)selectCellView:(UIButton *)button;
- (IBAction)playButtonPressed:(UIButton *)button;

@property (nonatomic, retain) IBOutlet UIView *cell1View;
@property (nonatomic, retain) IBOutlet UIView *cell2View;
@property int index;
@property (nonatomic, retain) CategoryViewController_iPhone *parentController;

@end
